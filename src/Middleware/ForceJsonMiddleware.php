<?php

namespace O360Main\SaasBridge\Middleware;

use Illuminate\Http\Request;
use Closure;
use Illuminate\Validation\ValidationException;


class ForceJsonMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');


        $response = $next($request);

        // if response is redirected
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return $response;
        }

        $statusCode = $response->getStatusCode();

        if ($response->isOk()) {
            return $response;
        }

        $json = [
            'status_code' => $statusCode,
            'message' => $response->exception?->getMessage() ?? 'Server error',
        ];

        if ($response->exception instanceof ValidationException) {
            $json = [
                'status_code' => $statusCode,
                'message' => 'Validation error',
                'errors' => $response->exception->errors(),
            ];
        }


        if ($request->headers->has('X-Plugin-Debug')) {

            $json['debug'] = [
                'url' => $request->url(),
                'method' => $request->method(),
                'headers' => $request->headers->all(),
                'request' => $request->all(),
                'trace' => $response?->exception?->getTrace() ?? [],
            ];
        }


        return response()->json($json, $statusCode);

    }
}
