<?php

declare(strict_types=1);

use Monorail\Forms\Components\Section;
use Monorail\Forms\Components\Tabs;
use Monorail\Forms\Components\TextInput;
use Monorail\Forms\Form;
use Monorail\Tests\Fixtures\WidgetResource;

it('serializes a Tabs container with each tab as a section schema', function () {
    $form = Form::make(WidgetResource::class)->schema([
        Tabs::make()->schema([
            Section::make('Content')->columns(2)->schema([
                TextInput::make('title')->required(),
            ]),
            Section::make('Meta')->schema([
                TextInput::make('slug')->required(),
            ]),
        ]),
    ]);

    $array = $form->toArray();
    $node = $array['fields'][0];

    expect($node['type'])->toBe('tabs');
    expect($node['tabs'])->toHaveCount(2);
    expect($node['tabs'][0]['label'])->toBe('Content');
    expect($node['tabs'][0]['columns'])->toBe(2);
    expect($node['tabs'][0]['fields'][0]['name'])->toBe('title');
    expect($node['tabs'][1]['label'])->toBe('Meta');
});

it('flattens fields across tabs for validation and defaults', function () {
    $form = Form::make(WidgetResource::class)->schema([
        Tabs::make()->schema([
            Section::make('Content')->schema([TextInput::make('title')->required()]),
            Section::make('Meta')->schema([TextInput::make('slug')->required()]),
        ]),
    ]);

    $rules = $form->getValidationRules();

    expect(array_keys($rules))->toBe(['title', 'slug']);
    expect($rules['title'])->toContain('required');
    expect($rules['slug'])->toContain('required');
});

it('coexists with top-level fields and Section siblings', function () {
    $form = Form::make(WidgetResource::class)->schema([
        TextInput::make('name'),
        Tabs::make()->schema([
            Section::make('A')->schema([TextInput::make('a')]),
            Section::make('B')->schema([TextInput::make('b')]),
        ]),
        Section::make('Misc')->schema([TextInput::make('notes')]),
    ]);

    expect(array_map(fn ($f) => $f->getName(), $form->getFields()))
        ->toBe(['name', 'a', 'b', 'notes']);
});
