<?php

declare(strict_types=1);

use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Monorail\Panel\Panel;
use Monorail\Panel\PanelManager;
use Monorail\Tests\Fixtures\DenyViewAnyWidgetPolicy;
use Monorail\Tests\Fixtures\OpenWidgetPolicy;
use Monorail\Tests\Fixtures\Widget;
use Monorail\Tests\Fixtures\WidgetResource;

beforeEach(function () {
    test()->actingAs(new GenericUser(['id' => 1]));

    Schema::dropIfExists('widgets');
    Schema::create('widgets', function ($table) {
        $table->id();
        $table->string('name');
        $table->softDeletes();
    });

    app(PanelManager::class)->register(
        Panel::make('test')
            ->path('test-admin')
            ->authMiddleware([])
            ->resources([WidgetResource::class])
    );
});

it('renders a 404 Inertia error page for unknown resource slugs', function () {
    Gate::policy(Widget::class, OpenWidgetPolicy::class);

    $response = test()
        ->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => 'monorail'])
        ->get('/test-admin/nope');

    $response->assertStatus(404);
    expect($response->headers->get('X-Inertia'))->toBe('true');
    expect($response->json('component'))->toBe('monorail/error');
    expect($response->json('props.status'))->toBe(404);
});

it('renders a 403 Inertia error page when a policy denies access', function () {
    Gate::policy(Widget::class, DenyViewAnyWidgetPolicy::class);

    $response = test()
        ->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => 'monorail'])
        ->get('/test-admin/widgets');

    $response->assertStatus(403);
    expect($response->json('component'))->toBe('monorail/error');
    expect($response->json('props.status'))->toBe(403);
});
