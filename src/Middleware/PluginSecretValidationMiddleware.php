<?php

namespace O360Main\SaasBridge\Middleware;

use Illuminate\Http\Request;
use Closure;
use O360Main\SaasBridge\Services\SaasCredentialsBoot;

class PluginSecretValidationMiddleware
{
    /**
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {

        try {
            $boot = new SaasCredentialsBoot($request);
            $boot->run();
            //-------
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        } finally {
            return $next($request);
        }
    }
}
