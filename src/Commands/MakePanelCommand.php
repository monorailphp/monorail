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
        $this->registerProvider("App\\Providers\\Monorail\\{$name}");

        return self::SUCCESS;
    }

    private function registerProvider(string $class): void
    {
        $path = base_path('bootstrap/providers.php');

        if (! file_exists($path)) {
            $this->warn('  Could not find bootstrap/providers.php — add manually:');
            $this->line("  {$class}::class,");

            return;
        }

        $content = file_get_contents($path);
        $entry = "{$class}::class";

        if (str_contains($content, $entry)) {
            $this->info('  Provider already registered.');

            return;
        }

        $content = preg_replace(
            '/^(\s*)(App\\\\Providers\\\\AppServiceProvider::class,)/m',
            "$1$2\n$1{$entry},",
            $content,
        );

        file_put_contents($path, $content);
        $this->info('  Provider registered in bootstrap/providers.php.');
    }
}
