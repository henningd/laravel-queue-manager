<?php

namespace HenningD\LaravelQueueManager;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class QueueManagerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/queue-manager.php', 'queue-manager'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/queue-manager.php' => config_path('queue-manager.php'),
        ], 'queue-manager-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'queue-manager-migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views/' => resource_path('views/vendor/queue-manager'),
        ], 'queue-manager-views');

        // Publish assets
        $this->publishes([
            __DIR__.'/../resources/assets/' => public_path('vendor/queue-manager'),
        ], 'queue-manager-assets');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'queue-manager');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\QueueManagerInstallCommand::class,
                Console\Commands\QueueManagerSeedCommand::class,
                Console\Commands\WorkerCreateCommand::class,
                Console\Commands\WorkerListCommand::class,
                Console\Commands\WorkerStartCommand::class,
            ]);
        }
    }
}