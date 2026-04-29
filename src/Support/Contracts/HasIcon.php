<?php

declare(strict_types=1);

namespace Monorail\Support\Contracts;

interface HasIcon
{
    public function getIcon(): ?string;
}
