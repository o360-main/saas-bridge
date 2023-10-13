<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'strict_mode' => env('SAAS_BRIDGE_STRICT_MODE', false),
    'main_version' => env('SAAS_BRIDGE_MAIN_VERSION', 'v1'),
    'saas_api_url' => env('SAAS_API_URL', 'http://core.o360-core.test'),
    'plugin_secret' => env('PLUGIN_SECRET', 'secret'),
    'token_validate_endpoint' => "/connection/validate",
    'manifest_path' => base_path('app/manifest.json'),
];
