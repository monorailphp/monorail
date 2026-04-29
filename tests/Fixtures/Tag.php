<?php

declare(strict_types=1);

namespace Monorail\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

final class Tag extends Model
{
    protected $guarded = [];

    public $timestamps = false;
}
