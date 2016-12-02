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
     *
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('Artme\Paysera\PayseraController');
        require_once(__DIR__.'/../lib/WebToPay.php');

        $namespace = config('paysera.order_model_namespace');

        if(!is_null($namespace)){
            if(class_exists($namespace)){
                if(!method_exists($namespace, 'setStatus')){
                    throw new \Exception('[laravel-paysera] '.$namespace.' model must have method setStatus($status)');
                }
            } else {
                throw new \Exception('[laravel-paysera] Order model set in paysera.php config doesn\'t exist');
            }
        }
    }
}
