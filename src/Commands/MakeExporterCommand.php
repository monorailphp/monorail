<?php

declare(strict_types=1);

namespace Monorail\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Monorail\Commands\Concerns\ResolvesStubs;

final class MakeExporterCommand extends Command
{
    use ResolvesStubs;

    protected $signature = 'monorail:make-exporter {name} {--model=}';

    protected $description = 'Create a new Monorail exporter class.';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));

        if (! Str::endsWith($name, 'Exporter')) {
            $name .= 'Exporter';
        }

        $model = (string) ($this->option('model') ?? Str::replaceLast('Exporter', '', $name));
        $modelFqcn = Str::contains($model, '\\') ? $model : "App\\Models\\{$model}";

        $path = app_path("Monorail/Exports/{$name}.php");

        if (file_exists($path)) {
            $this->error("Exporter [{$name}] already exists.");

            return self::FAILURE;
        }

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $this->renderStub('exporter', [
            'namespace' => 'App\\Monorail\\Exports',
            'class' => $name,
            'model' => class_basename($modelFqcn),
            'modelFqcn' => ltrim($modelFqcn, '\\'),
        ]));

        $this->info("Exporter [{$name}] created at {$path}.");

        return self::SUCCESS;
    }
}
