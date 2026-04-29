<?php

declare(strict_types=1);

namespace Monorail\Tests\Fixtures;

use Monorail\Imports\ImportColumn;
use Monorail\Imports\Importer;

final class WidgetImporter extends Importer
{
    protected static string $model = Widget::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->guess(['name', 'widget_name'])
                ->rules(['required', 'string', 'max:255'])
                ->example('Monorail'),
            ImportColumn::make('status')
                ->guess(['status', 'state'])
                ->rules(['required', 'in:active,inactive'])
                ->example('active'),
        ];
    }

    public function resolveRecord(): ?Widget
    {
        return new Widget;
    }

    public static function getChunkSize(): int
    {
        return 10;
    }
}
