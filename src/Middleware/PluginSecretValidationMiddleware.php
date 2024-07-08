<?php

namespace O360Main\SaasBridge\Middleware;

use Illuminate\Http\Request;
use Closure;
use O360Main\SaasBridge\SaasConfig;
use O360Main\SaasBridge\Services\SaasCredentialsBoot;

class PluginSecretValidationMiddleware
{
    /**
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        $token = config('saas-bridge.plugin_secret', null);

        abort_if(
            empty($token),
            401,
            'Plugin secret not set'
        );

        SaasCredentialsBoot::setEnvironment($request);

        if (SaasConfig::getInstance()->versionGreaterThenEqual("2.0.0")) {
            SaasCredentialsBoot::validateJwt($request);
        }

        //not required credentials
        $ignore = [
            'manifest.json',
            'ping',
        ];

        if (in_array($request->route()->uri, $ignore)) {
            return $next($request);
        }


        SaasCredentialsBoot::make($request)->run();
        return $next($request);

    }
}
