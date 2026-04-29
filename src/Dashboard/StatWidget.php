<?php

declare(strict_types=1);

namespace Monorail\Dashboard;

use Monorail\Dashboard\Concerns\CanRenderOnPages;
use Monorail\Support\Concerns\HasColumnSpan;

final class StatWidget
{
    use CanRenderOnPages;
    use HasColumnSpan;

    public function __construct(
        private readonly string $label,
        private readonly string|int|float $value,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => 'stat',
            'label' => $this->label,
            'value' => $this->value,
            'column_span' => $this->columnSpan,
        ];
    }
}
