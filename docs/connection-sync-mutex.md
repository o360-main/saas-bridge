# ConnectionSyncMutex - Distributed Locking

The `ConnectionSyncMutex` class provides Redis-based distributed locking mechanism similar to Go's `sync.Mutex`. It uses connection ID from request headers to ensure thread-safe operations across distributed systems.

## Basic Usage

```php
use O360Main\SaasBridge\Helpers\ConnectionSyncMutex;

// Create mutex with connection ID from request headers
$mutex = ConnectionSyncMutex::make('resource_key');

// Blocking lock with default timeout (30s)
$mutex->lock();
try {
    // Critical section - only one process can execute this
    $result = performCriticalOperation();
} finally {
    $mutex->unlock();
}
```

## Non-blocking Lock

```php
// Try to acquire lock without blocking
if ($mutex->tryLock()) {
    try {
        // Got the lock, proceed with work
        performWork();
    } finally {
        $mutex->unlock();
    }
} else {
    // Lock not available, handle accordingly
    return response()->json(['error' => 'Resource busy'], 423);
}
```

## Synchronized Execution

```php
// Automatic lock management with timeout
$result = ConnectionSyncMutex::make('sync_operation')->synchronized(function() {
    // This code runs exclusively
    return performSyncOperation();
}, 10); // 10 second timeout
```

## Configuration

The mutex can be configured via the published config file or environment variables.

### First-time Setup

```bash
# Publish the config file
php artisan vendor:publish --provider="O360Main\SaasBridge\SaasBridgeServiceProvider"
```

### Updating Existing Config

If you already have a published config file, you have several options:

#### Option 1: Force republish (overwrites your config)
```bash
php artisan vendor:publish --provider="O360Main\SaasBridge\SaasBridgeServiceProvider" --force
```

#### Option 2: Manually merge new config
Add the mutex configuration block to your existing `config/saas-bridge.php`:

```php
// Add this to your existing config/saas-bridge.php
'mutex' => [
    'ttl' => env('SAAS_MUTEX_TTL', 30),
    'retry_delay' => env('SAAS_MUTEX_RETRY_DELAY', 100),
    'max_retries' => env('SAAS_MUTEX_MAX_RETRIES', 100),
    'lock_timeout' => env('SAAS_MUTEX_LOCK_TIMEOUT', 30),
    'redis_connection' => env('SAAS_MUTEX_REDIS_CONNECTION', null),
    'key_prefix' => env('SAAS_MUTEX_KEY_PREFIX', 'saas_mutex'),
    'connection_headers' => [
        'X-Connection-ID',
        'Connection-ID', 
        'connection-id'
    ],
    'fallback_connection_id' => true,
],
```

#### Option 3: Use only environment variables (no config file changes needed)
```env
# Add these to your .env file
SAAS_MUTEX_TTL=30
SAAS_MUTEX_RETRY_DELAY=100
SAAS_MUTEX_MAX_RETRIES=100
SAAS_MUTEX_LOCK_TIMEOUT=30
SAAS_MUTEX_REDIS_CONNECTION=default
SAAS_MUTEX_KEY_PREFIX=saas_mutex
```

### Configuration Verification

```bash
# Check if mutex config is loaded
php artisan tinker
>>> config('saas-bridge.mutex')

# Clear config cache after changes
php artisan config:clear
```

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `SAAS_MUTEX_TTL` | Lock TTL in seconds | 30 |
| `SAAS_MUTEX_RETRY_DELAY` | Retry delay in milliseconds | 100 |
| `SAAS_MUTEX_MAX_RETRIES` | Maximum retry attempts | 100 |
| `SAAS_MUTEX_LOCK_TIMEOUT` | Lock acquisition timeout in seconds | 30 |
| `SAAS_MUTEX_REDIS_CONNECTION` | Redis connection name | null (default) |
| `SAAS_MUTEX_KEY_PREFIX` | Lock key prefix | saas_mutex |

## Custom Instance Configuration

```php
$mutex = new ConnectionSyncMutex(
    lockKey: 'custom_resource',
    connectionId: 'manual_connection_id', // Optional, auto-detected from headers
    ttl: 60,           // Override config TTL
    retryDelay: 50,    // Override config retry delay
    maxRetries: 200,   // Override config max retries
    lockTimeout: 45    // Override config lock timeout
);
```

## Connection ID Headers

The mutex automatically detects connection ID from these headers (in order):
- `X-Connection-ID`
- `Connection-ID` 
- `connection-id`

If no header is found, it generates one using `IP:PID`.

## Lock Information

```php
$info = $mutex->getLockInfo();
// Returns: ['key', 'connection_id', 'ttl', 'is_locked', 'lock_value']
```

## Error Handling

```php
try {
    $mutex->lock(5); // 5 second timeout
    // ... work ...
} catch (\RuntimeException $e) {
    // Lock acquisition failed after timeout
    logger()->error('Failed to acquire lock: ' . $e->getMessage());
} finally {
    $mutex->unlock();
}
```

## Troubleshooting

- If mutex settings aren't loading, ensure Redis is properly configured in your Laravel app
- Run `php artisan config:clear` after making config changes
- Check that the `saas-bridge.php` config file exists in your `config/` directory
- Verify Redis connection is working: `php artisan tinker` → `Redis::ping()`

## Version Upgrade Notes

- **ConnectionSyncMutex** is available from package version `x.x.x`
- If upgrading from older versions, existing config files won't have mutex settings
- Choose option 2 (manual merge) or 3 (env variables only) to avoid losing existing config
- The mutex will work with default settings even without config file updates

## Use Cases

### API Rate Limiting
```php
$mutex = ConnectionSyncMutex::make("rate_limit_user_{$userId}");
if (!$mutex->tryLock()) {
    return response()->json(['error' => 'Too many requests'], 429);
}
// Process request
$mutex->unlock();
```

### Data Synchronization
```php
$result = ConnectionSyncMutex::make('sync_products')->synchronized(function() {
    return syncProductsFromExternalAPI();
}, 300); // 5 minute timeout for long operations
```

### Cache Rebuilding
```php
$mutex = ConnectionSyncMutex::make('cache_rebuild');
if ($mutex->tryLock()) {
    try {
        rebuildExpensiveCache();
    } finally {
        $mutex->unlock();
    }
} else {
    // Another process is rebuilding, use stale cache
    return getCachedData();
}
```