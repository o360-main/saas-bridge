<?php

namespace O360Main\SaasBridge;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use O360Main\SaasBridge\Commands\CodeChecker;
use O360Main\SaasBridge\Commands\ConfigChecker;
use O360Main\SaasBridge\Commands\ControllerGenerator;
use O360Main\SaasBridge\Commands\KeyGenerator;
use O360Main\SaasBridge\Commands\SignatureGenerator;

class SaasBridgeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/saas-bridge.php' => config_path('saas-bridge.php'),
            ], 'config');

            //merge config
            $this->mergeConfigFrom(__DIR__ . '/../config/saas-bridge.php', 'saas-bridge');

            // $this->commands([]);
            $this->app->bind('saas:manifest-test', ConfigChecker::class);
            $this->app->bind('saas:code-test', CodeChecker::class);
            $this->app->bind('saas:generate:controller', ControllerGenerator::class);
            $this->app->bind('saas:generate:key', KeyGenerator::class);
            $this->app->bind('saas:generate:sign', SignatureGenerator::class);

            //load commands folder
            $this->commands([
                'saas:manifest-test',
                'saas:code-test',
                'saas:generate:controller',
                'saas:generate:key',
                'saas:generate:sign',
            ]);
        }

        foreach (glob(__DIR__ . '/Helpers/functions/*.php') as $filename) {
            require_once $filename;
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

            //            throw if $controller is not an instance of ControllerInterface

            if (config('saas-bridge.strict_mode')) {
                $controllerValidationService = new Services\ControllerValidationService;
                $controllerValidationService->validate($controller);
            }

            Route::post("/{$url}/data", [$controller, 'data']);
            Route::post("/{$url}/config", [$controller, 'config']);

            $pluginVersion = config('saas-bridge.plugin_version', 'v1');

            if ($pluginVersion == 'v1') {
                Route::post("/{$url}/import", [$controller, 'import']);
                Route::post("/{$url}/export", [$controller, 'export']);
            }

            Route::post("/{$url}/trigger", [$controller, 'trigger']);
        });
    }
}
