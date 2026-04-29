<?php

declare(strict_types=1);

namespace Monorail\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Monorail\Forms\Form;
use Monorail\Resources\Resource;
use Monorail\Tables\Table;

class SearchableWidgetResource extends Resource
{
    protected static string $model = Widget::class;

    protected static ?string $slug = 'widgets';

    protected static ?string $navigationIcon = 'package';

    public static function globalSearchColumns(): array
    {
        return ['name'];
    }

    public static function globalSearchResult(Model $record): array
    {
        return [
            'title' => $record->name,
            'description' => 'Widget #'.$record->getKey(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }
}
