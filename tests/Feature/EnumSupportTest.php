<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Monorail\Forms\Components\Select;
use Monorail\Support\Color;
use Monorail\Support\EnumSupport;
use Monorail\Tables\Columns\BadgeColumn;
use Monorail\Tables\Filters\SelectFilter;
use Monorail\Tests\Fixtures\Widget;
use Monorail\Tests\Fixtures\WidgetStatus;

it('extracts options from a backed enum using HasLabel', function () {
    expect(EnumSupport::toOptions(WidgetStatus::class))->toBe([
        'active' => 'Active Widget',
        'draft' => 'Draft Widget',
        'archived' => 'Archived Widget',
    ]);
});

it('extracts colors from a backed enum using HasColor and skips null colors', function () {
    expect(EnumSupport::toColors(WidgetStatus::class))->toBe([
        'active' => 'green',
        'draft' => 'slate',
    ]);
});

it('hydrates Select options from an enum and validates against enum values', function () {
    $select = Select::make('status')->enum(WidgetStatus::class)->required();

    expect($select->getValidationRules())->toContain('in:active,draft,archived');
});

it('hydrates SelectFilter options from an enum', function () {
    $filter = (new SelectFilter('status', 'status', 'Status'))->enum(WidgetStatus::class);

    $schema = $filter->toSchema(new Request);

    expect($schema['options'])->toBe([
        'active' => 'Active Widget',
        'draft' => 'Draft Widget',
        'archived' => 'Archived Widget',
    ]);
});

it('hydrates BadgeColumn colors from an enum and renders enum labels', function () {
    $column = BadgeColumn::make('status')->enum(WidgetStatus::class);

    expect($column->toArray()['extra']['colors'])->toBe([
        'active' => 'green',
        'draft' => 'slate',
    ]);

    $record = new Widget(['status' => 'active']);

    expect($column->getState($record))->toBe('active');
    expect($column->render($record))->toBe('Active Widget');
});

it('preserves enum value when BadgeColumn state is a UnitEnum instance', function () {
    $column = BadgeColumn::make('status')->enum(WidgetStatus::class);

    $record = new Widget;
    $record->setRawAttributes(['status' => WidgetStatus::Draft]);

    expect($column->getState($record))->toBe('draft');
    expect($column->render($record))->toBe('Draft Widget');
});

it('accepts Color instances in BadgeColumn colors() and serializes tokens, passing raw hex through', function () {
    $column = BadgeColumn::make('status')->colors([
        'active' => Color::Green,
        'draft' => '#abcdef',
    ]);

    expect($column->toArray()['extra']['colors'])->toBe([
        'active' => 'green',
        'draft' => '#abcdef',
    ]);
});

it('throws when a non-enum class is passed to enum helpers', function () {
    EnumSupport::toOptions(stdClass::class);
})->throws(InvalidArgumentException::class);
