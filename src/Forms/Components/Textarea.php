<?php

declare(strict_types=1);

namespace Monorail\Forms\Components;

final class Textarea extends Field
{
    private int $rows = 4;

    public function rows(int $rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    public function type(): string
    {
        return 'textarea';
    }

    protected function typeRules(): array
    {
        return ['string'];
    }

    protected function extraProps(): array
    {
        return ['rows' => $this->rows];
    }
}
