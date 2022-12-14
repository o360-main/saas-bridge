<?php

namespace O360Main\SaasBridge;

use Illuminate\Support\ServiceProvider;
use O360Main\SaasBridge\Commands\ConfigChecker;
use O360Main\SaasBridge\Services\SaasHttpClient;

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

            $this->app->bind('saas:config-check', ConfigChecker::class);

            //load commands folder
            $this->commands([
                'saas:config-check',
            ]);
        }



    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'saas_bridge');


        // Register the main class to use with the facade
        $this->app->singleton('saas-bridge', function () {
            return new SaasBridgeService;
        });

        $this->app->bind(SaasAgent::class, function () {
            return SaasAgent::getInstance();
        });

        $this->app->bind(PluginConfig::class, function () {
            return new PluginConfig();
        });


    }
}
