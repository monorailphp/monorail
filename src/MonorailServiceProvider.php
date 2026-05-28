<?php

declare(strict_types=1);

namespace Monorail;

use Illuminate\Support\ServiceProvider;
use Monorail\Commands\InstallCommand;
use Monorail\Commands\MakeExporterCommand;
use Monorail\Commands\MakeImporterCommand;
use Monorail\Commands\MakePageCommand;
use Monorail\Commands\MakePanelCommand;
use Monorail\Commands\MakeResourceCommand;
use Monorail\Commands\SyncAssetsCommand;
use Monorail\Panel\PanelManager;

final class MonorailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/monorail.php', 'monorail');

        $this->app->singleton(PanelManager::class, fn () => new PanelManager);
        $this->app->alias(PanelManager::class, 'monorail');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'monorail');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/monorail.php');

        app('translator')->addJsonPath(__DIR__.'/../lang');

        $this->publishes([
            __DIR__.'/../config/monorail.php' => config_path('monorail.php'),
        ], 'monorail-config');

        $this->publishes([
            __DIR__.'/../resources/js' => resource_path('js/vendor/monorailphp'),
        ], 'monorail-assets');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/monorail'),
        ], 'monorail-views');

        $this->publishes([
            __DIR__.'/../lang' => lang_path('vendor/monorail'),
        ], 'monorail-lang');

        $this->publishes([
            __DIR__.'/../stubs' => base_path('stubs/monorail'),
        ], 'monorail-stubs');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'monorail-migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                MakePanelCommand::class,
                MakeResourceCommand::class,
                MakePageCommand::class,
                MakeExporterCommand::class,
                MakeImporterCommand::class,
                SyncAssetsCommand::class,
            ]);
        }
    }
}
