<?php

declare(strict_types=1);

namespace Monorail\Tests\Fixtures;

use Monorail\Resources\RelationManagers\RelationManager;
use Monorail\Tables\Columns\TextColumn;
use Monorail\Tables\Filters\SelectFilter;
use Monorail\Tables\Table;

final class CommentsRelationManager extends RelationManager
{
    public static function getRelationship(): string
    {
        return 'comments';
    }

    public static function getRelatedModel(): string
    {
        return Comment::class;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('body')->sortable(),
                TextColumn::make('status'),
            ])
            ->searchable(['body'])
            ->filters([
                new SelectFilter('status', 'status', 'Status', [
                    'approved' => 'Approved',
                    'pending' => 'Pending',
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
