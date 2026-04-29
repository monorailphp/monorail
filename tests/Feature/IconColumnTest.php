<?php

declare(strict_types=1);

use Monorail\Support\Color;
use Monorail\Tables\Columns\IconColumn;
use Monorail\Tests\Fixtures\Widget;
use Monorail\Tests\Fixtures\WidgetStatus;

it('serializes icon and color defaults', function () {
    $schema = IconColumn::make('status')->icon('star')->color(Color::Amber)->size(24)->toArray();

    expect($schema['type'])->toBe('icon');
    expect($schema['extra'])->toBe([
        'icon' => 'star',
        'icons' => [],
        'color' => 'amber',
        'colors' => [],
        'size' => 24,
    ]);
});

it('maps icons and colors by state', function () {
    $schema = IconColumn::make('priority')
        ->icons(['low' => 'arrow-down', 'high' => 'arrow-up'])
        ->colors(['low' => Color::Slate, 'high' => 'red'])
        ->toArray();

    expect($schema['extra']['icons'])->toBe(['low' => 'arrow-down', 'high' => 'arrow-up']);
    expect($schema['extra']['colors'])->toBe(['low' => 'slate', 'high' => 'red']);
});

it('hydrates icons and colors from a HasIcon enum', function () {
    $schema = IconColumn::make('status')->enum(WidgetStatus::class)->toArray();

    expect($schema['extra']['icons'])->toBe([
        'active' => 'check-circle',
        'draft' => 'pencil',
    ]);
    expect($schema['extra']['colors'])->toBe([
        'active' => 'green',
        'draft' => 'slate',
    ]);
});

it('unwraps a UnitEnum state to its backing value', function () {
    $column = IconColumn::make('status')->enum(WidgetStatus::class);
    $record = new Widget(['status' => WidgetStatus::Active]);

    expect($column->getState($record))->toBe('active');
});
