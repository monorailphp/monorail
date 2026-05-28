<?php

declare(strict_types=1);

namespace Monorail\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

final class SyncAssetsCommand extends Command
{
    protected $signature = 'monorail:sync-assets
        {--skip-install : Update package.json but do not run npm install}
        {--quiet : Suppress informational output}';

    protected $description = "Sync the host app's monorailphp npm package version to match the installed Composer package.";

    public function handle(): int
    {
        $vendorPackage = base_path('vendor/monorailphp/monorail/package.json');
        $hostPackage = base_path('package.json');

        if (! is_file($vendorPackage)) {
            $this->components->error('Could not locate vendor/monorailphp/monorail/package.json. Is the package installed?');

            return self::FAILURE;
        }

        if (! is_file($hostPackage)) {
            $this->components->error('No package.json found in the application root.');

            return self::FAILURE;
        }

        $vendorVersion = $this->readVersion($vendorPackage);

        if ($vendorVersion === null) {
            $this->components->error('vendor/monorailphp/monorail/package.json does not declare a version.');

            return self::FAILURE;
        }

        $host = json_decode((string) file_get_contents($hostPackage), true);

        if (! is_array($host)) {
            $this->components->error('package.json is not valid JSON.');

            return self::FAILURE;
        }

        $desired = '^'.$vendorVersion;
        $current = $host['dependencies']['monorailphp']
            ?? $host['devDependencies']['monorailphp']
            ?? null;

        if ($current === $desired) {
            $this->info("monorailphp is already pinned to {$desired}; nothing to do.");

            return self::SUCCESS;
        }

        $host['dependencies'] ??= [];
        $host['dependencies']['monorailphp'] = $desired;

        file_put_contents(
            $hostPackage,
            json_encode($host, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL,
        );

        $this->info(sprintf(
            'Updated package.json: monorailphp %s → %s',
            $current ?? '(missing)',
            $desired,
        ));

        if ($this->option('skip-install')) {
            $this->line('Skipping npm install (--skip-install).');

            return self::SUCCESS;
        }

        return $this->runNpmInstall();
    }

    private function readVersion(string $path): ?string
    {
        $data = json_decode((string) file_get_contents($path), true);

        if (! is_array($data) || ! isset($data['version']) || ! is_string($data['version'])) {
            return null;
        }

        return $data['version'];
    }

    private function runNpmInstall(): int
    {
        $this->line('Running: npm install monorailphp');

        $process = Process::fromShellCommandLine('npm install monorailphp');
        $process->setTimeout(180);
        $process->run(function ($type, $buffer): void {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            $this->components->error('npm install failed.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
