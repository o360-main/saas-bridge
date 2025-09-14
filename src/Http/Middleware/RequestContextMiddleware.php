<?php

namespace O360Main\SaasBridge\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use O360Main\SaasBridge\Helpers\RequestResponseContext;

class RequestContextMiddleware
{
    /**
     * Handle an incoming request and outgoing response
     */
    public function handle(Request $request, Closure $next)
    {
        // Initialize context from request headers
        RequestResponseContext::getInstance()->initFromRequest($request);
        
        // Process the request
        $response = $next($request);
        
        // Auto-attach processing stats to JSON responses
        if ($response instanceof JsonResponse) {
            $config = config('saas-bridge.request_context.auto_attach', true);
            
            if ($config) {
                RequestResponseContext::getInstance()->attachToResponse($response);
            }
        }
        
        return $response;
    }
}