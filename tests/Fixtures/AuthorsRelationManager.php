<?php

declare(strict_types=1);

namespace Monorail\Tests\Fixtures;

use Monorail\Resources\RelationManagers\RelationManager;
use Monorail\Tables\Columns\TextColumn;
use Monorail\Tables\Table;

final class AuthorsRelationManager extends RelationManager
{
    public static function getRelationship(): string
    {
        return 'authors';
    }

    public static function getRelatedModel(): string
    {
        return Author::class;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->sortable(),
            ])
            ->defaultSort('name', 'asc');
    }
}
