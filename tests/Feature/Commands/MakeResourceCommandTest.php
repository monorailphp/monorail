<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    $this->resourceDir = app_path('Monorail/Resources');

    if (is_dir($this->resourceDir)) {
        foreach (glob($this->resourceDir.'/*.php') as $file) {
            @unlink($file);
        }
    }
});

it('generates a resource from the stub', function () {
    $exitCode = Artisan::call('monorail:make-resource', ['name' => 'Post']);

    expect($exitCode)->toBe(0);

    $path = app_path('Monorail/Resources/PostResource.php');
    expect($path)->toBeFile();

    $contents = file_get_contents($path);
    expect($contents)
        ->toContain('namespace App\\Monorail\\Resources;')
        ->toContain('use App\\Models\\Post;')
        ->toContain('final class PostResource extends Resource')
        ->toContain('protected static string $model = Post::class;');
});

it('accepts a custom model fqcn', function () {
    Artisan::call('monorail:make-resource', [
        'name' => 'Book',
        '--model' => 'Domain\\Library\\Book',
    ]);

    $contents = file_get_contents(app_path('Monorail/Resources/BookResource.php'));
    expect($contents)
        ->toContain('use Domain\\Library\\Book;')
        ->toContain('protected static string $model = Book::class;');
});
