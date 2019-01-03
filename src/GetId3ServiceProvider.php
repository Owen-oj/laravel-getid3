<?php

namespace Owenoj\LaravelGetId3;

use Illuminate\Support\ServiceProvider;

class GetId3ServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'owen-oj');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'owen-oj');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-getid3.php', 'laravel-getid3');

        // Register the service the package provides.
        $this->app->singleton('GetId3', function ($app) {
            return new GetId3();
        });

        $this->app->alias('GetId3', 'Owenoj\LaravelGetId3\GetId3');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laravel-getid3'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/laravel-getid3.php' => config_path('laravel-getid3.php'),
        ], 'laravel-getid3.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/owen-oj'),
        ], 'laravel-getid3.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/owen-oj'),
        ], 'laravel-getid3.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/owen-oj'),
        ], 'laravel-getid3.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
