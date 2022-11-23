<?php

namespace O360Main\SaasBridge\Middleware;

use Illuminate\Http\Request;
use Closure;
use O360Main\SaasBridge\SaasAgent;
use O360Main\SaasBridge\SaasBridge;

class SaasBridgeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        $validate = app(SaasAgent::class)
            ->setAuth(['token' => $token])
            ->validate();

        if (!$validate) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
