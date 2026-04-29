<?php

declare(strict_types=1);

use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Monorail\Panel\Panel;
use Monorail\Panel\PanelManager;
use Monorail\Tables\Actions\ViewAction;
use Monorail\Tests\Fixtures\OpenWidgetPolicy;
use Monorail\Tests\Fixtures\Widget;
use Monorail\Tests\Fixtures\WidgetResource;

it('exposes link action metadata and eye icon', function () {
    $schema = ViewAction::make()->toArray();

    expect($schema)->toMatchArray([
        'name' => 'view',
        'label' => 'View',
        'icon' => 'eye',
        'link' => true,
        'route_suffix' => 'view',
        'ability' => 'view',
        'requires_confirmation' => false,
    ]);
});

it('renders the ViewRecord page through the /view route', function () {
    Gate::policy(Widget::class, OpenWidgetPolicy::class);
    test()->actingAs(new GenericUser(['id' => 1]));

    Schema::dropIfExists('widgets');
    Schema::create('widgets', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('status')->default('active');
        $table->boolean('is_featured')->default(false);
        $table->date('published_at')->nullable();
        $table->softDeletes();
    });

    app(PanelManager::class)->register(
        Panel::make('test')
            ->path('test-admin')
            ->authMiddleware([])
            ->resources([WidgetResource::class])
    );

    $widget = Widget::create(['name' => 'Alpha']);

    $payload = inertiaGet('/test-admin/widgets/'.$widget->getKey().'/view')->json();

    expect($payload['component'])->toBe('monorail/view-record');
    expect($payload['props']['record']['key'])->toBe($widget->getKey());
    expect($payload['props']['state'])->toBeArray();
    expect($payload['props']['form']['fields'])->toBeArray();
});
