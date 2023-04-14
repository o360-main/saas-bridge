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

            $secret = $request->header('X-Plugin-Secret');



        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        } finally {
            return $next($request);
        }
    }
}
