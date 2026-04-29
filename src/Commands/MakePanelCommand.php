<?php

declare(strict_types=1);

namespace Monorail\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Monorail\Commands\Concerns\ResolvesStubs;

final class MakePanelCommand extends Command
{
    use ResolvesStubs;

    protected $signature = 'monorail:make-panel {name}';

    protected $description = 'Create a new Monorail panel provider.';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));

        if (! Str::endsWith($name, 'PanelProvider')) {
            $name .= 'PanelProvider';
        }

        $id = Str::of($name)
            ->replaceLast('PanelProvider', '')
            ->kebab()
            ->lower()
            ->value();

        $path = app_path("Providers/Monorail/{$name}.php");

        if (file_exists($path)) {
            $this->error("Panel provider [{$name}] already exists.");

            return self::FAILURE;
        }

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $this->renderStub('panel', [
            'namespace' => 'App\\Providers\\Monorail',
            'class' => $name,
            'id' => $id,
        ]));

        $this->info("Panel provider [{$name}] created at {$path}.");
        $this->line('');
        $this->line('Next: register it in bootstrap/providers.php:');
        $this->line("  App\\Providers\\Monorail\\{$name}::class,");

        return self::SUCCESS;
    }
}
