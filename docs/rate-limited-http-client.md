# RateLimitedHttpClient - Rate Limited HTTP Wrapper

The `RateLimitedHttpClient` provides a wrapper around Laravel's HTTP client with built-in rate limiting, Redis-based storage, and automatic blocking when limits are exceeded using ConnectionSyncMutex.

## Features

- **Rate Limiting**: Configurable requests per time window
- **Redis Storage**: Connection-specific rate limit tracking
- **Automatic Blocking**: Uses ConnectionSyncMutex to wait when rate limited
- **API-specific Limits**: Different limits per API endpoint
- **Batch Operations**: Execute multiple requests with shared rate limiting
- **Full HTTP Support**: GET, POST, PUT, PATCH, DELETE, HEAD methods

## Basic Usage

### Simple HTTP Requests

```php
use O360Main\SaasBridge\Helpers\RateLimitedHttpClient;

// Create client with connection ID from headers
$client = RateLimitedHttpClient::make();

// Make requests - automatically rate limited
$response = $client->get('https://api.example.com/users');
$response = $client->post('https://api.example.com/users', ['name' => 'John']);
$response = $client->put('https://api.example.com/users/1', ['name' => 'Jane']);
$response = $client->delete('https://api.example.com/users/1');
```

### API-specific Rate Limits

```php
// Use predefined API configuration
$shopifyClient = RateLimitedHttpClient::forApi('shopify');
$stripeClient = RateLimitedHttpClient::forApi('stripe');

// Each client has its own rate limits
$products = $shopifyClient->get('/admin/api/2023-01/products.json');
$customers = $stripeClient->get('/v1/customers');
```

### Batch Operations

```php
$requests = [
    'users' => ['method' => 'GET', 'url' => '/api/users'],
    'orders' => ['method' => 'GET', 'url' => '/api/orders'],
    'products' => ['method' => 'POST', 'url' => '/api/products', 'data' => ['name' => 'New Product']],
];

$responses = $client->batch($requests);
// Returns: ['users' => Response, 'orders' => Response, 'products' => Response]
```

### Custom Rate Limiting

```php
$client = RateLimitedHttpClient::make()
    ->configure([
        'max_requests' => 30,
        'window_seconds' => 60,
        'block_when_exhausted' => false, // Throw exception instead of waiting
        'retry_after_seconds' => 120
    ]);

$response = $client->get('https://api.example.com/data');
```

## Configuration

### First-time Setup

```bash
# Publish the config file
php artisan vendor:publish --provider="O360Main\SaasBridge\SaasBridgeServiceProvider"
```

### Environment Variables

```env
# Rate Limiter Configuration
SAAS_RATE_LIMIT_MAX_REQUESTS=60
SAAS_RATE_LIMIT_WINDOW_SECONDS=60
SAAS_RATE_LIMIT_BLOCK_WHEN_EXHAUSTED=true
SAAS_RATE_LIMIT_RETRY_AFTER=60
SAAS_RATE_LIMIT_WAIT_TIMEOUT=300
SAAS_RATE_LIMIT_REDIS_CONNECTION=default
SAAS_RATE_LIMIT_KEY_PREFIX=rate_limit

# HTTP Client Configuration
SAAS_HTTP_TIMEOUT=30
SAAS_HTTP_RETRIES=3
SAAS_HTTP_RETRY_DELAY=100

# API-specific Rate Limits
SHOPIFY_API_RATE_LIMIT=40
STRIPE_API_RATE_LIMIT=100
EXTERNAL_API_RATE_LIMIT=30
```

### Config File (config/saas-bridge.php)

```php
'rate_limiter' => [
    'max_requests' => env('SAAS_RATE_LIMIT_MAX_REQUESTS', 60),
    'window_seconds' => env('SAAS_RATE_LIMIT_WINDOW_SECONDS', 60),
    'block_when_exhausted' => env('SAAS_RATE_LIMIT_BLOCK_WHEN_EXHAUSTED', true),
    'retry_after_seconds' => env('SAAS_RATE_LIMIT_RETRY_AFTER', 60),
    'wait_timeout' => env('SAAS_RATE_LIMIT_WAIT_TIMEOUT', 300),
    'redis_connection' => env('SAAS_RATE_LIMIT_REDIS_CONNECTION', null),
    'key_prefix' => env('SAAS_RATE_LIMIT_KEY_PREFIX', 'rate_limit'),
    'timeout' => env('SAAS_HTTP_TIMEOUT', 30),
    'retries' => env('SAAS_HTTP_RETRIES', 3),
    'retry_delay' => env('SAAS_HTTP_RETRY_DELAY', 100),
    'connection_headers' => [
        'X-Connection-ID',
        'Connection-ID', 
        'connection-id'
    ],
    'fallback_connection_id' => true,
],

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
],
```

## Advanced Usage

### Manual Rate Limit Management

```php
// Check current rate limit status
$status = $client->getRateLimitStatus();
/*
Returns:
[
    'connection_id' => 'abc123',
    'rate_limit_key' => 'rate_limit:abc123',
    'max_requests' => 60,
    'window_seconds' => 60,
    'current_requests' => 15,
    'remaining_requests' => 45,
    'reset_time' => 1634567890,
    'seconds_until_reset' => 30,
    'is_exhausted' => false
]
*/

// Reset rate limit (admin/testing)
$client->resetRateLimit();

// Get client configuration
$config = $client->getConfig();
```

### Custom Callback with Rate Limiting

```php
$result = $client->rateLimited(function($httpClient) {
    // $httpClient is Laravel's PendingRequest instance
    return $httpClient->withHeaders(['X-API-Key' => 'secret'])
                     ->get('https://api.example.com/protected-endpoint');
});
```

### Blocking vs Non-blocking Behavior

```php
// Blocking mode (default) - waits when rate limited
$client = RateLimitedHttpClient::make()->configure([
    'block_when_exhausted' => true,
    'retry_after_seconds' => 60,
    'wait_timeout' => 300
]);

// Non-blocking mode - throws exception when rate limited
$client = RateLimitedHttpClient::make()->configure([
    'block_when_exhausted' => false
]);

try {
    $response = $client->get('/api/data');
} catch (\RuntimeException $e) {
    // Handle rate limit exceeded
    if (str_contains($e->getMessage(), 'Rate limit exceeded')) {
        // Wait and retry, or handle accordingly
    }
}
```

## Connection ID Headers

The client automatically detects connection ID from these headers (in priority order):

1. `X-Connection-ID`
2. `Connection-ID`
3. `connection-id`

If no header is found, it generates a fallback: `{IP}:{PID}`

## Rate Limiting Algorithm

- **Sliding Window**: Uses time-based windows for rate limiting
- **Redis Storage**: Atomic increment operations ensure accuracy
- **Connection-based**: Each connection ID has independent limits
- **Automatic Expiration**: Rate limit counters expire automatically

## Blocking Mechanism

When `block_when_exhausted` is true:

1. **Mutex Protection**: Uses ConnectionSyncMutex to prevent thundering herd
2. **Smart Waiting**: Only waits the remaining time until reset
3. **Timeout Protection**: Maximum wait time to prevent indefinite blocking
4. **Double-checking**: Re-verifies rate limit after acquiring mutex

## Integration Examples

### Laravel Controller

```php
class ApiController extends Controller
{
    public function fetchExternalData(Request $request)
    {
        $client = RateLimitedHttpClient::make($request);
        
        try {
            $response = $client->get('https://external-api.com/data');
            return response()->json($response->json());
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 429);
        }
    }
}
```

### Artisan Command

```php
class SyncDataCommand extends Command
{
    public function handle()
    {
        $client = RateLimitedHttpClient::forApi('shopify');
        
        $requests = [
            'products' => ['method' => 'GET', 'url' => '/admin/api/2023-01/products.json'],
            'customers' => ['method' => 'GET', 'url' => '/admin/api/2023-01/customers.json'],
            'orders' => ['method' => 'GET', 'url' => '/admin/api/2023-01/orders.json'],
        ];
        
        $responses = $client->batch($requests);
        
        foreach ($responses as $type => $response) {
            $this->info("Synced {$type}: " . count($response->json()[$type] ?? []));
        }
    }
}
```

### Queue Job

```php
class ProcessApiRequestJob implements ShouldQueue
{
    public function handle()
    {
        $client = RateLimitedHttpClient::make()
            ->configure(['max_requests' => 10, 'window_seconds' => 60]);
        
        $response = $client->post('/api/webhook', $this->data);
        
        if (!$response->successful()) {
            throw new \Exception('API request failed: ' . $response->body());
        }
    }
}
```

## Monitoring and Debugging

### Rate Limit Status Monitoring

```php
// Log rate limit status for monitoring
$status = $client->getRateLimitStatus();
logger()->info('Rate limit status', $status);

// Alert when approaching limit
if ($status['remaining_requests'] < 5) {
    logger()->warning('Rate limit nearly exhausted', $status);
}
```

### Custom Redis Connection

```php
// Use specific Redis connection for rate limiting
$client = RateLimitedHttpClient::make(null, [
    'redis_connection' => 'rate_limiting'
]);
```

## Troubleshooting

### Rate Limits Not Working

1. **Check Redis Connection**:
   ```bash
   php artisan tinker
   >>> Redis::ping()
   ```

2. **Verify Configuration**:
   ```bash
   php artisan config:clear
   php artisan tinker
   >>> config('saas-bridge.rate_limiter')
   ```

3. **Check Connection ID**:
   ```php
   $client = RateLimitedHttpClient::make();
   dd($client->getConfig()['connection_id']);
   ```

### Rate Limiting Too Aggressive

- Increase `max_requests` or `window_seconds`
- Use API-specific configurations for different endpoints
- Consider using non-blocking mode for non-critical requests

### Blocking Too Long

- Reduce `wait_timeout` for maximum wait time
- Decrease `retry_after_seconds` for faster retries
- Use non-blocking mode and implement custom retry logic

### Memory Issues with Many Connections

- Configure Redis key expiration properly
- Use shorter `window_seconds` for faster cleanup
- Monitor Redis memory usage

## Performance Considerations

- **Redis Performance**: Rate limiting adds minimal overhead (~1ms per request)
- **Blocking Efficiency**: Uses mutex to prevent multiple processes waiting unnecessarily
- **Memory Usage**: Each connection uses minimal Redis memory (one key per time window)
- **Cleanup**: Rate limit keys automatically expire, no manual cleanup needed