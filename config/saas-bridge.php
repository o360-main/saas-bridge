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

    /*
     * Rate Limiter Configuration
     * HTTP request rate limiting with Redis-based storage
     */
    'rate_limiter' => [
        // Maximum requests per time window
        'max_requests' => env('SAAS_RATE_LIMIT_MAX_REQUESTS', 60),
        
        // Time window in seconds
        'window_seconds' => env('SAAS_RATE_LIMIT_WINDOW_SECONDS', 60),
        
        // Block and wait when rate limit is exhausted (true) or throw exception (false)
        'block_when_exhausted' => env('SAAS_RATE_LIMIT_BLOCK_WHEN_EXHAUSTED', true),
        
        // Seconds to wait before retrying when rate limited
        'retry_after_seconds' => env('SAAS_RATE_LIMIT_RETRY_AFTER', 60),
        
        // Maximum timeout for waiting when blocked (seconds)
        'wait_timeout' => env('SAAS_RATE_LIMIT_WAIT_TIMEOUT', 300),
        
        // Redis connection name to use (null for default)
        'redis_connection' => env('SAAS_RATE_LIMIT_REDIS_CONNECTION', null),
        
        // Key prefix for rate limit counters
        'key_prefix' => env('SAAS_RATE_LIMIT_KEY_PREFIX', 'rate_limit'),
        
        // HTTP client default configuration
        'timeout' => env('SAAS_HTTP_TIMEOUT', 30),
        'retries' => env('SAAS_HTTP_RETRIES', 3),
        'retry_delay' => env('SAAS_HTTP_RETRY_DELAY', 100),
        
        // Connection ID headers priority (first found will be used)
        'connection_headers' => [
            'X-Connection-ID',
            'Connection-ID', 
            'connection-id'
        ],
        
        // Fallback connection ID generation when no header present
        'fallback_connection_id' => true, // Generate IP:PID if no header
    ],

    /*
     * API-specific Rate Limits
     * Configure different rate limits for specific APIs
     */
    'api_rate_limits' => [
        'shopify' => [
            'max_requests' => env('SHOPIFY_API_RATE_LIMIT', 40),
            'window_seconds' => 60,
            'block_when_exhausted' => true,
        ],
        
        'stripe' => [
            'max_requests' => env('STRIPE_API_RATE_LIMIT', 100),
            'window_seconds' => 60,
            'block_when_exhausted' => false,
        ],
        
        'external_api' => [
            'max_requests' => env('EXTERNAL_API_RATE_LIMIT', 30),
            'window_seconds' => 60,
            'block_when_exhausted' => true,
            'retry_after_seconds' => 120,
        ],
    ],

    /*
     * Request/Response Context Configuration
     * Track processing data per request with connection/record IDs
     */
    'request_context' => [
        // Header names to extract IDs from (in priority order)
        'headers' => [
            'connection_id' => ['X-Connection-ID', 'Connection-ID', 'connection-id'],
            'record_id' => ['X-Record-ID', 'Record-ID', 'record-id'],
            'record_log_id' => ['X-Record-Log-ID', 'Record-Log-ID', 'record-log-id'],
        ],
        
        // Auto-attach processing stats to JSON responses
        'auto_attach' => env('SAAS_CONTEXT_AUTO_ATTACH', true),
        
        // How to attach stats: 'header', 'body', or 'both'
        'attach_mode' => env('SAAS_CONTEXT_ATTACH_MODE', 'header'),
        
        // Redis configuration for context storage
        'redis_ttl' => env('SAAS_CONTEXT_REDIS_TTL', 3600), // 1 hour
        'redis_key_prefix' => env('SAAS_CONTEXT_KEY_PREFIX', 'request_context'),
        'redis_connection' => env('SAAS_CONTEXT_REDIS_CONNECTION', null),
        
        // Enable/disable context tracking
        'enabled' => env('SAAS_CONTEXT_ENABLED', true),
        
        // Log processing stats (for monitoring/debugging)
        'log_stats' => env('SAAS_CONTEXT_LOG_STATS', false),
        'log_channel' => env('SAAS_CONTEXT_LOG_CHANNEL', 'default'),
    ],

    /*
     * SaasApi HTTP Client Configuration
     * HTTP client settings for SaasApiClient
     */
    'saas_api' => [
        // Request timeout in seconds (10 minutes = 600 seconds)
        'timeout' => env('SAAS_API_TIMEOUT', 600),
        
        // Maximum response size in bytes (10GB = 10737418240 bytes)
        'max_response_size' => env('SAAS_API_MAX_RESPONSE_SIZE', 10737418240),
    ],
];
