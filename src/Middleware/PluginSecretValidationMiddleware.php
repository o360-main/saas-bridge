<?php

namespace O360Main\SaasBridge\Middleware;

use Illuminate\Http\Request;
use Closure;

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

        return $next($request);

    }
}
