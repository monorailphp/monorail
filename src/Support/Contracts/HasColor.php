<?php

declare(strict_types=1);

namespace Monorail\Support\Contracts;

use Monorail\Support\Color;

interface HasColor
{
    public function getColor(): Color|string|null;
}
