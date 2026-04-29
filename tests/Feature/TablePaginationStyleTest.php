<?php

declare(strict_types=1);

use Monorail\Tables\Table;
use Monorail\Tests\Fixtures\WidgetResource;

it('defaults pagination_style to the configured value', function () {
    config()->set('monorail.pagination.style', 'numbered');

    $schema = Table::make(WidgetResource::class)->toArray();

    expect($schema['pagination_style'])->toBe('numbered');
});

it('falls back to numbered when config value is invalid', function () {
    config()->set('monorail.pagination.style', 'bogus');

    $schema = Table::make(WidgetResource::class)->toArray();

    expect($schema['pagination_style'])->toBe('numbered');
});

it('honors a per-table paginationStyle override', function () {
    config()->set('monorail.pagination.style', 'numbered');

    $schema = Table::make(WidgetResource::class)
        ->paginationStyle('compact')
        ->toArray();

    expect($schema['pagination_style'])->toBe('compact');
});

it('ignores invalid override and falls back to config', function () {
    config()->set('monorail.pagination.style', 'simple');

    $schema = Table::make(WidgetResource::class)
        ->paginationStyle('not-a-style')
        ->toArray();

    expect($schema['pagination_style'])->toBe('simple');
});
