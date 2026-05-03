<?php

declare(strict_types=1);

it('runs monorail:install command successfully', function () {
    $exitCode = Illuminate\Support\Facades\Artisan::call('monorail:install');

    expect($exitCode)->toBe(0);
});

it('runs monorail:install command and outputs steps', function () {
    $exitCode = Artisan::call('monorail:install');

    expect($exitCode)->toBe(0);
});
