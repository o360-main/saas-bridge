<?php

namespace O360Main\SaasBridge;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use O360Main\SaasBridge\Commands\CodeChecker;
use O360Main\SaasBridge\Commands\ConfigChecker;
use O360Main\SaasBridge\Commands\ControllerGenerator;
use O360Main\SaasBridge\Commands\KeyGenerator;
use O360Main\SaasBridge\Commands\SignatureGenerator;
use O360Main\SaasBridge\Helpers\RequestResponseContext;

class SaasBridgeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/saas-bridge.php' => config_path('saas-bridge.php'),
            ], 'config');

            //merge config
            $this->mergeConfigFrom(__DIR__.'/../config/saas-bridge.php', 'saas-bridge');

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

        foreach (glob(__DIR__.'/Helpers/functions/*.php') as $filename) {
            require_once $filename;
        }
        
        // Register request macro for processing tracking
        $this->registerRequestMacros();

    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/saas-bridge.php', 'saas-bridge');

        // Register the main class to use with the facade

        //        $this->app->singleton('saas-bridge', static function () {
        //            return new SaasBridgeService();
        //        });
        //
        //        $this->app->bind(SaasAgent::class, static function () {
        //            return SaasAgent::getInstance();
        //        });

        // Register RequestResponseContext as singleton
        $this->app->singleton(RequestResponseContext::class, function () {
            return RequestResponseContext::getInstance();
        });

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
    
    /**
     * Register Request macros for processing tracking
     */
    protected function registerRequestMacros(): void
    {
        Request::macro('track', function() {
            return RequestResponseContext::getInstance();
        });
        
        Request::macro('trackSaasId', function(string $saasId, bool $success = true, ?string $error = null) {
            RequestResponseContext::saasId($saasId, $success, $error);
            return $this;
        });
        
        Request::macro('trackSyncId', function(string $syncId, bool $success = true, ?string $error = null) {
            RequestResponseContext::syncId($syncId, $success, $error);
            return $this;
        });
        
        Request::macro('trackPlatformId', function(string $platformId, bool $success = true, ?string $error = null) {
            RequestResponseContext::platformId($platformId, $success, $error);
            return $this;
        });
        
        Request::macro('trackCustom', function(string $type, string $id, bool $success = true, ?string $error = null, array $metadata = []) {
            RequestResponseContext::custom($type, $id, $success, $error, $metadata);
            return $this;
        });
        
        Request::macro('trackApiResponse', function(array $response, string $type = 'api_call') {
            RequestResponseContext::apiResponse($response, $type);
            return $this;
        });
        
        Request::macro('incrementProcessed', function(int $count = 1) {
            RequestResponseContext::processed($count);
            return $this;
        });
        
        Request::macro('addTrackingError', function(string $type, string $message, ?string $id = null) {
            RequestResponseContext::error($type, $message, $id);
            return $this;
        });
        
        Request::macro('getProcessingStats', function() {
            return RequestResponseContext::getInstance()->getProcessingStats();
        });
    }
}
