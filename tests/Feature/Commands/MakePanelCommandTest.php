<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    $this->panelDir = app_path('Providers/Monorail');

    if (is_dir($this->panelDir)) {
        foreach (glob($this->panelDir.'/*.php') as $file) {
            @unlink($file);
        }
    }
});

it('generates a panel provider from the stub', function () {
    $exitCode = Artisan::call('monorail:make-panel', ['name' => 'Admin']);

    expect($exitCode)->toBe(0);

    $path = app_path('Providers/Monorail/AdminPanelProvider.php');
    expect($path)->toBeFile();

    $contents = file_get_contents($path);
    expect($contents)
        ->toContain('namespace App\\Providers\\Monorail;')
        ->toContain('final class AdminPanelProvider extends PanelProvider')
        ->toContain("->path('admin')")
        ->toContain("->brand('admin')");
});

it('fails when the panel provider already exists', function () {
    Artisan::call('monorail:make-panel', ['name' => 'Admin']);
    $exitCode = Artisan::call('monorail:make-panel', ['name' => 'Admin']);

    expect($exitCode)->toBe(1);
});
