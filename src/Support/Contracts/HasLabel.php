<?php

declare(strict_types=1);

namespace Monorail\Support\Contracts;

interface HasLabel
{
    public function getLabel(): string;
}
