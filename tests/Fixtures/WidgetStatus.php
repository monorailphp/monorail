<?php

declare(strict_types=1);

namespace Monorail\Tests\Fixtures;

use Monorail\Support\Color;
use Monorail\Support\Contracts\HasColor;
use Monorail\Support\Contracts\HasIcon;
use Monorail\Support\Contracts\HasLabel;

enum WidgetStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';
    case Draft = 'draft';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active Widget',
            self::Draft => 'Draft Widget',
            self::Archived => 'Archived Widget',
        };
    }

    public function getColor(): Color|string|null
    {
        return match ($this) {
            self::Active => Color::Green,
            self::Draft => Color::Slate,
            self::Archived => null,
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Active => 'check-circle',
            self::Draft => 'pencil',
            self::Archived => null,
        };
    }
}
