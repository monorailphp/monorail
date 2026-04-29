<?php

declare(strict_types=1);

namespace Monorail\Forms\Components;

final class Checkbox extends Field
{
    public function type(): string
    {
        return 'checkbox';
    }

    protected function typeRules(): array
    {
        return ['boolean'];
    }
}
