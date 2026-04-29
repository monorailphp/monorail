<?php

declare(strict_types=1);

use Monorail\Tables\Actions\DeleteAction;
use Monorail\Tables\Actions\EditAction;

it('exposes link action metadata in the serialized schema', function () {
    $schema = EditAction::make()->toArray();

    expect($schema)->toMatchArray([
        'name' => 'edit',
        'label' => 'Edit',
        'requires_confirmation' => false,
        'destructive' => false,
        'icon' => 'pencil',
        'scope' => 'row',
        'link' => true,
        'route_suffix' => 'edit',
        'ability' => 'update',
    ]);
});

it('keeps DeleteAction as a non-link action', function () {
    $schema = DeleteAction::make()->toArray();

    expect($schema['link'])->toBeFalse();
    expect($schema['route_suffix'])->toBeNull();
    expect($schema['ability'])->toBeNull();
});
