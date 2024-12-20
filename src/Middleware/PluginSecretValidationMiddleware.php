<?php

namespace O360Main\SaasBridge\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use O360Main\SaasBridge\SaasConfig;
use O360Main\SaasBridge\Services\SaasCredentialsBoot;

class PluginSecretValidationMiddleware
{
    /**
     * @throws Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        $token = config('saas-bridge.plugin_secret', null);

        abort_if(
            empty($token),
            401,
            'Plugin secret not set [0]'
        );

        SaasCredentialsBoot::setEnvironment($request);

        if (SaasConfig::getInstance()->versionGreaterThenEqual('2.0.0')) {
            SaasCredentialsBoot::validateJwt($request);
        }

        //not required credentials
        $ignore = [
            'manifest.json',
            'ping',
        ];

        $uri = $request->route()->uri;
        $endUri = explode('/', $uri);
        $uri = end($endUri);

        foreach ($ignore as $item) {
            if (strtolower($uri) == $item) {
                return $next($request);
            }
        }

        SaasCredentialsBoot::make($request)->run();

        return $next($request);

    }
}
