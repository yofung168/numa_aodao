<?php

namespace Numa\Aodao;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Boot the provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();
        $this->publishes([
            __DIR__."/config.php"=>config_path('aodao.php')
        ]);
    }

    /**
     * Setup the config.
     *
     * @return void
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__ . '/config.php');
        $this->mergeConfigFrom($source, 'aodao');
    }

    /**
     * Register the provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('aodao', function ($app) {
            return new Aodao(config('aodao'));
        });
    }
}
