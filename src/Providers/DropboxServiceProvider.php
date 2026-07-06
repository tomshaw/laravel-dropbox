<?php

namespace TomShaw\Dropbox\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use TomShaw\Dropbox\{DropboxClient, DropboxManager};
use TomShaw\Dropbox\Middlewares\DropboxConnect;

class DropboxServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $this->loadMigrations();
        $this->publishConfig();
        $this->registerMiddleware($router);
    }

    public function register(): void
    {
        $this->mergeConfig();
        $this->bindServices();
    }

    protected function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    protected function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/config.php' => config_path('dropbox.php'),
            ], 'config');
        }
    }

    protected function registerMiddleware(Router $router): void
    {
        $router->aliasMiddleware('dropbox', DropboxConnect::class);
    }

    protected function mergeConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'dropbox');
    }

    protected function bindServices(): void
    {
        $this->app->singleton(DropboxClient::class, fn (): DropboxClient => new DropboxClient);

        $this->app->singleton(DropboxManager::class);
    }
}
