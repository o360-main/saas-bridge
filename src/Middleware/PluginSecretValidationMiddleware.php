<?php

namespace O360Main\SaasBridge\Middleware;

use Illuminate\Http\Request;
use Closure;
use O360Main\SaasBridge\Services\SaasCredentialsBoot;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class PluginSecretValidationMiddleware
{
    /**
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
//        try {
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
