<?php

namespace O360Main\SaasBridge\Middleware;

use Closure;
use Illuminate\Http\Request;
use O360Main\SaasBridge\Services\EncService;

class RSAAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $rsa = EncService::getInstance();

        return $next($request);
    }
}
