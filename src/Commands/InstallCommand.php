<?php

declare(strict_types=1);

namespace Monorail\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

final class InstallCommand extends Command
{
    protected $signature = 'monorail:install {--force : Overwrite existing files}';

    protected $description = 'Install Monorail in a Laravel project';

    public function handle(): int
    {
        $this->info('Installing Monorail frontend...');
        $this->line('');

        $this->info('Step 1: Installing npm package...');
        $this->runNpmInstall();

        $this->info('Step 2: Publishing config...');
        $this->publishConfig();

        $this->info('Step 3: Updating vite.config.ts...');
        $this->updateViteConfig();

        $this->info('Step 4: Publishing migrations...');
        $this->publishMigrations();

        $this->info('Step 5: Running migrations...');
        $this->runMigrations();

        $this->line('');
        $this->info('Monorail installed successfully!');
        $this->line('');
        $this->line('Next: Create a panel and resource:');
        $this->line('  composer require monorailphp/monorail');
        $this->line('  php artisan monorail:make-panel Admin');
        $this->line('  php artisan monorail:make-resource User');

        return self::SUCCESS;
    }

    private function runNpmInstall(): void
    {
        $this->line('  Running: npm install monorailphp');

        $process = Process::fromShellCommandLine('npm install monorailphp');
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('  npm install failed: '.$process->getErrorOutput());

            return;
        }

        $this->info('  npm package installed.');
    }

    private function publishConfig(): void
    {
        $process = Process::fromShellCommandLine('php artisan vendor:publish --provider="Monorail\MonorailServiceProvider" --tag=monorail-config --no-interaction');
        $process->run();

        if ($process->isSuccessful()) {
            $this->info('  Config published.');
        } else {
            $this->warn('  Config publish skipped (may already exist).');
        }
    }

    private function updateViteConfig(): void
    {
        $viteConfigPath = base_path('vite.config.ts');

        if (! File::exists($viteConfigPath)) {
            $this->warn('  vite.config.ts not found - skipping.');

            return;
        }

        $content = File::get($viteConfigPath);

        $entryPoint = "'node_modules/monorailphp/resources/js/monorail.tsx'";

        if (str_contains($content, $entryPoint)) {
            $this->info('  vite.config.ts already updated.');

            return;
        }

        // Match app.tsx whether it's the last item in a single-line array (no trailing comma)
        // or has a trailing comma in a multi-line array. Handles both ' and " quoting.
        $injected = false;
        $patterns = [
            "'resources/js/app.tsx'," => "'resources/js/app.tsx',\n            {$entryPoint},",
            "'resources/js/app.tsx']" => "'resources/js/app.tsx', {$entryPoint}]",
            '"resources/js/app.tsx",' => "\"resources/js/app.tsx\",\n            {$entryPoint},",
            '"resources/js/app.tsx"]' => "\"resources/js/app.tsx\", {$entryPoint}]",
        ];

        foreach ($patterns as $search => $replace) {
            if (str_contains($content, $search)) {
                $content = str_replace($search, $replace, $content);
                $injected = true;
                break;
            }
        }

        if (! $injected) {
            $this->warn('  Could not find app.tsx in vite.config.ts - please add the entry manually:');
            $this->line("    {$entryPoint},");

            return;
        }

        // Inject @monorail alias — handle existing alias block, missing alias block, or no resolve block at all.
        if (! str_contains($content, '@monorail')) {
            $aliasEntry = "'@monorail': path.resolve(__dirname, 'node_modules/monorailphp/resources/js')";

            if (str_contains($content, 'alias: {')) {
                $content = str_replace('alias: {', "alias: {\n            {$aliasEntry},", $content);
            } elseif (str_contains($content, 'resolve: {')) {
                $content = str_replace('resolve: {', "resolve: {\n        alias: {\n            {$aliasEntry},\n        },", $content);
            } else {
                // No resolve block — inject before the closing of defineConfig
                $content = str_replace('});', "    resolve: {\n        alias: {\n            {$aliasEntry},\n        },\n    },\n});", $content);
            }
        }

        File::put($viteConfigPath, $content);
        $this->info('  vite.config.ts updated.');
    }

    private function publishMigrations(): void
    {
        $process = Process::fromShellCommandLine('php artisan vendor:publish --provider="Monorail\MonorailServiceProvider" --tag=monorail-migrations --no-interaction');
        $process->run();

        if ($process->isSuccessful()) {
            $this->info('  Migrations published.');
        } else {
            $this->warn('  Migrations publish skipped.');
        }
    }

    private function runMigrations(): void
    {
        $process = Process::fromShellCommandLine('php artisan migrate --no-interaction');
        $process->run();

        if ($process->isSuccessful()) {
            $this->info('  Database tables created.');
        } else {
            $this->warn('  Migration skipped (tables may already exist).');
        }
    }
}
