<?php

namespace O360Main\SaasBridge\Middleware;

use Illuminate\Http\Request;
use Closure;
use O360Main\SaasBridge\SaasAgent;
use O360Main\SaasBridge\Services\SaasCredentialsBoot;
use O360Main\SaasBridge\Services\SaasHttpClient;

class SaasBridgeMiddleware
{

    /**
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        if (!function_exists('ray')) {
            function ray(...$args)
            {
                return $args;
            }
        }

        try {
            //ray request
            ray($request->url(), $request->all());

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
