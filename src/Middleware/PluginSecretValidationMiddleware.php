<?php

namespace O360Main\SaasBridge\Middleware;

use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Config;

class PluginSecretValidationMiddleware
{
    /**
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $token = config('saas-bridge.plugin_secret');

        abort_if(
            empty($token),
            401,
            'Plugin secret not set'
        );

        abort_if(
            $request->header('X-Plugin-Secret') != $token,
            401,
            'Invalid secret'
        );

        Config::set('saas-bridge.plugin_dev', $request->header('X-Plugin-Dev', false));
        Config::set('saas-bridge.main_version', $request->header('X-Main-Version', 'v1'));


        return $next($request);

    }
}
