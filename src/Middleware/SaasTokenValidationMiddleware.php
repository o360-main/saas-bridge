<?php

namespace O360Main\SaasBridge\Middleware;

use Closure;
use Illuminate\Http\Request;
use O360Main\SaasBridge\SaasConfig;
use O360Main\SaasBridge\Services\SaasCredentialsBootV1;

class SaasTokenValidationMiddleware
{
    /**
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {

        if (SaasConfig::getInstance()->versionGreaterThenEqual('2.0.0')) {
            // ignore
            return $next($request);
        }

        try {

            // v1
            SaasCredentialsBootV1::make($request)->run();

            return $next($request);

        } catch (\Exception $e) {

            return response()->json(['message' => $e->getMessage()], 401);
        }
    }
}
