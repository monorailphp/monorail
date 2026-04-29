<?php

declare(strict_types=1);

namespace Monorail\Tests\Fixtures;

use Monorail\Exports\ExportColumn;
use Monorail\Exports\Exporter;

final class WidgetExporter extends Exporter
{
    protected static string $model = Widget::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('name')->label('Name'),
            ExportColumn::make('status')->label('Status'),
        ];
    }

    public static function getChunkSize(): int
    {
        return 10;
    }
}
