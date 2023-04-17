<?php

namespace O360Main\SaasBridge\Middleware;

use Illuminate\Http\Request;
use Closure;
use O360Main\SaasBridge\Services\SaasCredentialsBoot;

class SaasTokenValidationMiddleware
{
    /**
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            SaasCredentialsBoot::make($request)->run();

            return $next($request);

        } catch (\Exception $e) {

            return response()->json(['message' => $e->getMessage()], 401);
        }
    }
}
