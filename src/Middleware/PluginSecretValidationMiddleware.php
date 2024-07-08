<?php

namespace O360Main\SaasBridge\Middleware;

use Illuminate\Http\Request;
use Closure;
use O360Main\SaasBridge\SaasConfig;
use O360Main\SaasBridge\Services\SaasCredentialsBoot;

class PluginSecretValidationMiddleware
{
    /**
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        $token = config('saas-bridge.plugin_secret', null);


        $environment = $request->input('_env', []);

        if (empty($environment)) {
            abort(401, 'Environment not set');
        }

        \config()->set('saas-bridge.main_version', $environment['version'] ?? "1.0.0");
        \config()->set('saas-bridge.plugin_dev', $environment['dev_mode'] ?? false);
        \config()->set('saas-bridge.debug', $environment['debug'] ?? false);
        \config()->set('saas-bridge.core_url', $environment['core_url'] ?? null);
        \config()->set('saas-bridge.pass_headers', $environment['pass'] ?? []);


        //throw  errors if all values are not set


        $request->request->remove('_env');

        if (SaasConfig::getInstance()->versionGreaterThenEqual("2.0.0")) {
            SaasCredentialsBoot::validateJwt($request);
            return $next($request);
        }

        abort_if(
            empty($token),
            401,
            'Plugin secret not set'
        );


        //not required credentials
        $ignore = [
            'manifest.json',
            'ping',
        ];

        if (in_array($request->route()->uri, $ignore)) {
            return $next($request);
        }

        try {
            SaasCredentialsBoot::make($request)->run();
            return $next($request);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }

    }
}
