<?php

namespace Owenoj\LaravelGetId3;

use Illuminate\Support\ServiceProvider;

class GetId3ServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // Register the service the package provides.
        $this->app->singleton('GetId3', function ($app) {
            return new GetId3();
        });

        $this->app->alias('GetId3', 'Owenoj\LaravelGetId3\GetId3');
    }
}
