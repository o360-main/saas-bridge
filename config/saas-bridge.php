<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'strict_mode' => env('SAAS_BRIDGE_STRICT_MODE', false),
    //    'main_version' => env('SAAS_BRIDGE_MAIN_VERSION', 'v1'),
    //    'plugin_dev' => false,
    'main_version' => '1.0.0',
    //    'core_url' => env('SAAS_API_URL', 'http://core.o360-core.test'),
    'plugin_secret' => env('PLUGIN_SECRET', 'secret'),
    'plugin_version' => env('PLUGIN_VERSION', 'v1'),
    //    'token_validate_endpoint' => "/connection/validate",
    //    'manifest_path' => base_path('app/manifest.json'),

    'telescope_token' => env('TELESCOPE_TOKEN', null),

    /*
     * ConnectionSyncMutex Configuration
     * Redis-based distributed locking configuration
     */
    'mutex' => [
        // Default TTL for locks in seconds
        'ttl' => env('SAAS_MUTEX_TTL', 30),
        
        // Retry delay between lock attempts in milliseconds
        'retry_delay' => env('SAAS_MUTEX_RETRY_DELAY', 100),
        
        // Maximum number of retry attempts
        'max_retries' => env('SAAS_MUTEX_MAX_RETRIES', 100),
        
        // Default timeout for blocking lock operations in seconds
        'lock_timeout' => env('SAAS_MUTEX_LOCK_TIMEOUT', 30),
        
        // Redis connection name to use (null for default)
        'redis_connection' => env('SAAS_MUTEX_REDIS_CONNECTION', null),
        
        // Key prefix for all mutex locks
        'key_prefix' => env('SAAS_MUTEX_KEY_PREFIX', 'saas_mutex'),
        
        // Connection ID headers priority (first found will be used)
        'connection_headers' => [
            'X-Connection-ID',
            'Connection-ID', 
            'connection-id'
        ],
        
        // Fallback connection ID generation when no header present
        'fallback_connection_id' => true, // Generate IP:PID if no header
    ],
];
