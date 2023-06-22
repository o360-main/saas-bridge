<?php

namespace O360Main\SaasBridge;

use Illuminate\Support\ServiceProvider;
use O360Main\SaasBridge\Commands\ConfigChecker;
use Illuminate\Support\Facades\Route;

class SaasBridgeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/saas-bridge.php' => config_path('saas-bridge.php'),
            ], 'config');

            // $this->commands([]);

            $this->app->bind('saas:manifest-test', ConfigChecker::class);

            //load commands folder
            $this->commands([
                'saas:manifest-test',
            ]);
        }


    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/saas-bridge.php', 'saas-bridge');

        // Register the main class to use with the facade

        //        $this->app->singleton('saas-bridge', static function () {
        //            return new SaasBridgeService();
        //        });
        //
        //        $this->app->bind(SaasAgent::class, static function () {
        //            return SaasAgent::getInstance();
        //        });

        //make route macro

        Route::macro('module', function ($url, $controller) {
            //Categories
            Route::post("/{$url}/config", [$controller, 'config']);
            Route::post("/{$url}/import", [$controller, 'import']);
            Route::post("/{$url}/export", [$controller, 'export']);
            Route::post("/{$url}/trigger", [$controller, 'trigger']);
        });
    }
}
