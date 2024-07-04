<?php

namespace O360Main\SaasBridge\Middleware;

use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Log;
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

        /**
         * @var \Illuminate\Http\Response $response
         */
        $statusCode = $response->getStatusCode();

        if ($response->isSuccessful()) {
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
                'error_msg' => $response->exception?->getMessage() ?? "Validation error",
                'errors' => $response->exception?->errors() ?? [],
            ];
        }


        try {
            if ($request->headers->has('X-Plugin-Debug')) {

                $json['debug'] = [
                    'url' => $request->url(),
                    'method' => $request->method(),
                    'headers' => $request->headers->all(),
                    'request' => $request->all(),
                    'trace' => $response?->exception?->getTrace() ?? [],
                ];
            }

        } catch (\Exception $e) {
            //            Log::error($e->getMessage(), $e->getTrace());
            $json['debug'] = [
                'error' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ];
        }

        return response()->json($json, $statusCode);

    }
}
