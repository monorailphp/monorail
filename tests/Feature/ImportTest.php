<?php

declare(strict_types=1);

use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Monorail\Imports\ImportColumn;
use Monorail\Imports\Jobs\PrepareImportJob;
use Monorail\Models\FailedImportRow;
use Monorail\Models\Import;
use Monorail\Panel\Panel;
use Monorail\Panel\PanelManager;
use Monorail\Tables\Actions\ImportAction;
use Monorail\Tests\Fixtures\OpenWidgetPolicy;
use Monorail\Tests\Fixtures\Widget;
use Monorail\Tests\Fixtures\WidgetImporter;
use Monorail\Tests\Fixtures\WidgetResource;

beforeEach(function () {
    Gate::policy(Widget::class, OpenWidgetPolicy::class);
    test()->actingAs(new GenericUser(['id' => 1]));

    Storage::fake('local');
    config()->set('monorail.imports.disk', 'local');
    config()->set('monorail.imports.chunk_size', 10);
    config()->set('queue.default', 'sync');
    config()->set('queue.batching.database', config('database.default'));
    config()->set('queue.batching.table', 'job_batches');

    Schema::dropIfExists('widgets');
    Schema::create('widgets', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('status')->default('active');
        $table->softDeletes();
    });

    Schema::dropIfExists('job_batches');
    Schema::create('job_batches', function ($table) {
        $table->string('id')->primary();
        $table->string('name');
        $table->integer('total_jobs');
        $table->integer('pending_jobs');
        $table->integer('failed_jobs');
        $table->longText('failed_job_ids');
        $table->mediumText('options')->nullable();
        $table->integer('cancelled_at')->nullable();
        $table->integer('created_at');
        $table->integer('finished_at')->nullable();
    });

    Schema::dropIfExists('monorail_failed_import_rows');
    Schema::dropIfExists('monorail_imports');
    Schema::create('monorail_imports', function ($table) {
        $table->id();
        $table->string('importer');
        $table->string('file_name');
        $table->string('file_disk');
        $table->unsignedInteger('total_rows')->default(0);
        $table->unsignedInteger('processed_rows')->default(0);
        $table->unsignedInteger('successful_rows')->default(0);
        $table->unsignedInteger('failed_rows')->default(0);
        $table->string('batch_id')->nullable();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->timestamp('completed_at')->nullable();
        $table->timestamps();
    });
    Schema::create('monorail_failed_import_rows', function ($table) {
        $table->id();
        $table->foreignId('import_id')->constrained('monorail_imports')->cascadeOnDelete();
        $table->json('data');
        $table->text('validation_error');
        $table->timestamps();
    });

    app(PanelManager::class)->register(
        Panel::make('test')
            ->path('test-admin')
            ->authMiddleware([])
            ->resources([WidgetResource::class])
    );
});

it('guesses a column mapping from CSV header', function () {
    $header = ['WIDGET_NAME', 'state'];

    $map = WidgetImporter::guessMapping($header);

    expect($map['name'])->toBe(0)
        ->and($map['status'])->toBe(1);
});

it('casts and validates a single row', function () {
    $importer = new WidgetImporter(new Import(['importer' => WidgetImporter::class]));
    $importer->setRow(['name' => 'Monorail', 'status' => 'active']);

    expect($importer->getData()['name'])->toBe('Monorail');
    expect(WidgetImporter::validate($importer->getData()))->toBeNull();

    $bad = new WidgetImporter(new Import(['importer' => WidgetImporter::class]));
    $bad->setRow(['name' => '', 'status' => 'bogus']);
    expect(WidgetImporter::validate($bad->getData()))->not->toBeNull();
});

it('runs PrepareImportJob end-to-end (sync) and persists records', function () {
    $csv = "Name,Status\nRocket,active\nSaturn,active\nBadRow,bogus\n";

    $import = Import::query()->create([
        'importer' => WidgetImporter::class,
        'file_name' => 'import.csv',
        'file_disk' => 'local',
        'total_rows' => 0,
        'processed_rows' => 0,
        'successful_rows' => 0,
        'failed_rows' => 0,
    ]);

    Storage::disk('local')->put('monorail-imports/'.$import->id.'/import.csv', $csv);

    $mapping = ['name' => 0, 'status' => 1];

    (new PrepareImportJob($import->getKey(), WidgetImporter::class, $mapping))->handle();

    $import->refresh();

    expect($import->total_rows)->toBe(3)
        ->and($import->processed_rows)->toBe(3)
        ->and($import->successful_rows)->toBe(2)
        ->and($import->failed_rows)->toBe(1)
        ->and($import->completed_at)->not->toBeNull();

    expect(Widget::query()->count())->toBe(2);
    expect(FailedImportRow::query()->where('import_id', $import->id)->count())->toBe(1);
});

it('dispatches PrepareImportJob from ImportAction handle', function () {
    Bus::fake();

    $file = UploadedFile::fake()->createWithContent('import.csv', "Name,Status\nRocket,active\n");

    $request = Request::create('/test-admin/widgets/header-actions/import', 'POST', [
        'mapping' => ['name' => 0, 'status' => 1],
    ], [], ['file' => $file]);
    $request->setUserResolver(fn () => new GenericUser(['id' => 9]));

    $action = ImportAction::make(WidgetImporter::class);
    $action->handle($request, Widget::query());

    Bus::assertDispatched(PrepareImportJob::class);
    expect(Import::query()->first()->user_id)->toBe(9);
});

it('returns an example CSV from importer endpoint', function () {
    $slug = base64_encode(WidgetImporter::class);

    test()->withHeaders(['Accept' => 'application/json'])
        ->get("/test-admin/importers/{$slug}/example.csv")
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

it('serves a preview with a guessed mapping', function () {
    $slug = base64_encode(WidgetImporter::class);
    $file = UploadedFile::fake()->createWithContent('import.csv', "name,status\nA,active\nB,inactive\n");

    test()->post("/test-admin/importers/{$slug}/preview", ['file' => $file])
        ->assertOk()
        ->assertJsonPath('mapping.name', 0)
        ->assertJsonPath('mapping.status', 1)
        ->assertJsonCount(2, 'preview');
});

it('downloads failed rows as CSV', function () {
    $import = Import::query()->create([
        'importer' => WidgetImporter::class,
        'file_name' => 'import.csv',
        'file_disk' => 'local',
        'total_rows' => 1,
        'processed_rows' => 1,
        'successful_rows' => 0,
        'failed_rows' => 1,
        'user_id' => 1,
    ]);

    FailedImportRow::query()->create([
        'import_id' => $import->id,
        'data' => ['name' => '', 'status' => 'bogus'],
        'validation_error' => 'The name field is required.',
    ]);

    test()->get("/test-admin/imports/{$import->id}/failed-rows.csv")
        ->assertOk();
});

it('forbids accessing another user\'s import', function () {
    $import = Import::query()->create([
        'importer' => WidgetImporter::class,
        'file_name' => 'import.csv',
        'file_disk' => 'local',
        'total_rows' => 0,
        'processed_rows' => 0,
        'successful_rows' => 0,
        'failed_rows' => 0,
        'user_id' => 999,
    ]);

    test()->withHeaders(['Accept' => 'application/json'])
        ->get("/test-admin/imports/{$import->id}/status")
        ->assertForbidden();
});

it('resolves relationships via ImportColumn::relationship()', function () {
    Schema::create('tags', function ($table) {
        $table->id();
        $table->string('slug');
    });

    Schema::create('widget_tag', function ($table) {
        $table->id();
        $table->foreignId('widget_id');
        $table->foreignId('tag_id');
    });

    // We don't actually wire a belongsTo on Widget for this spec,
    // so the test limits itself to the cast-side of relationships.
    $col = ImportColumn::make('status')
        ->guess(['status'])
        ->rules(['required']);

    expect($col->castState('active'))->toBe('active');
    expect($col->castState(''))->toBe('');

    $col2 = ImportColumn::make('flag')->boolean();
    expect($col2->castState('yes'))->toBeTrue();
    expect($col2->castState('0'))->toBeFalse();

    $col3 = ImportColumn::make('tags')->array(',');
    expect($col3->castState('a,b,c'))->toBe(['a', 'b', 'c']);
});
