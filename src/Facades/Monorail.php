<?php

declare(strict_types=1);

namespace Monorail\Facades;

use Illuminate\Support\Facades\Facade;
use Monorail\Panel\Panel;
use Monorail\Panel\PanelManager;

/**
 * @method static Panel panel(string $id)
 * @method static Panel register(Panel $panel)
 * @method static Panel get(string $id)
 * @method static Panel getCurrent()
 * @method static void setCurrent(string $id)
 * @method static array all()
 */
final class Monorail extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PanelManager::class;
    }
}
