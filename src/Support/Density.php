<?php

declare(strict_types=1);

namespace Monorail\Support;

enum Density: string
{
    case Compact = 'compact';
    case Default = 'default';
    case Comfortable = 'comfortable';
}
