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
        $this->app->alias('GetId3', 'Owenoj\LaravelGetId3\GetId3');
    }
}
