<?php

namespace Artme\Paysera;

use Illuminate\Support\ServiceProvider;

include __DIR__.'/routes.php';

class PayseraServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('paysera.php')
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('Artme\Paysera\PayseraController');
        require_once(__DIR__.'/../lib/WebToPay.php');
    }
}
