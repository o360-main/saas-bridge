<?php

namespace O360Main\SaasBridge;

use Illuminate\Support\ServiceProvider;

class SaasBridgeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('saas-bridge.php'),
            ], 'config');

            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'saas-bridge');

        // Register the main class to use with the facade
        $this->app->singleton('saas-bridge', function () {
            return new SaasBridgeService;
        });

        $this->app->singleton(SaasAgent::class, function () {
            return new SaasAgent();
        });
    }
}
