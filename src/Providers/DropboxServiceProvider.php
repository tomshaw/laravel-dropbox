<?php

namespace TomShaw\Dropbox\Providers;

use GuzzleHttp\Client;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use TomShaw\Dropbox\DropboxClient;
use TomShaw\Dropbox\Middlewares\{DropboxConnect};

class DropboxServiceProvider extends ServiceProvider
{
    public function boot(Router $router)
    {
        $this->loadMigrations();
        $this->publishConfig();
        $this->registerMiddleware($router);
    }

    public function register()
    {
        $this->mergeConfig();
        $this->bindDropboxClient();
    }

    protected function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    protected function publishConfig()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/config.php' => config_path('dropbox.php'),
            ], 'config');
        }
    }

    protected function registerMiddleware(Router $router)
    {
        $router->aliasMiddleware('dropbox', DropboxConnect::class);
    }

    protected function mergeConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'dropbox');
    }

    protected function bindDropboxClient()
    {
        $this->app->singleton(DropboxClient::class, fn () => new DropboxClient(new Client()));
    }
}
