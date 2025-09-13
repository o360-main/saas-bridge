# HttpDataMapper - Laravel HTTP Response Transformation

The `HttpDataMapper` extends JsonDataMapper to work seamlessly with Laravel HTTP client responses, providing powerful transformation capabilities for API integrations.

## Basic Usage

### Transform HTTP Response

```php
use O360Main\SaasBridge\Helpers\HttpDataMapper;
use Illuminate\Support\Facades\Http;

// Make API request
$response = Http::get('https://api.example.com/users');

// Transform response
$mapper = HttpDataMapper::create([
    'id' => ['source' => 'user_id', 'transform' => 'string'],
    'name' => 'full_name',
    'email' => ['source' => 'email_address', 'transform' => 'lowercase'],
    'created_at' => ['source' => 'created_time', 'transform' => 'api_timestamp']
]);

$transformedData = $mapper->transformResponse($response);
// Returns PHP array
```

### Array to Array Transformation

```php
// Direct PHP array transformation (no JSON involved)
$sourceArray = [
    'user_id' => 123,
    'full_name' => 'John Doe',
    'email_address' => 'JOHN@EXAMPLE.COM'
];

$mapper = HttpDataMapper::create([
    'id' => ['source' => 'user_id', 'transform' => 'string'],
    'name' => 'full_name',
    'email' => ['source' => 'email_address', 'transform' => 'lowercase']
]);

$result = $mapper->transformArray($sourceArray);
// Returns: ['id' => '123', 'name' => 'John Doe', 'email' => 'john@example.com']
```

## HTTP-Specific Features

### API Endpoint Mapper

```php
// Pre-configured mapper for specific API endpoints
$mapper = HttpDataMapper::forApiEndpoint('users', [
    'id' => 'user_id',
    'name' => 'display_name',
    'balance' => ['source' => 'balance_cents', 'transform' => 'api_money'], // Converts cents to dollars
    'is_active' => ['source' => 'status', 'transform' => 'api_boolean'],
    'last_login' => ['source' => 'last_login_timestamp', 'transform' => 'api_timestamp']
]);

$response = Http::get('https://api.example.com/users/123');
$user = $mapper->transformResponse($response);
```

### Response with Metadata

```php
$mapper = HttpDataMapper::create($rules);

// Transform with HTTP metadata
$result = $mapper->transformResponseWithMeta($response, [
    'rate_limit' => 'X-RateLimit-Remaining',
    'request_id' => 'X-Request-ID'
]);

// Result includes:
// - data: transformed response body
// - http_meta: status, headers, content info
// - transformed_at: timestamp
```

### Paginated API Responses

```php
$response = Http::get('https://api.example.com/products?page=1');

$mapper = HttpDataMapper::create([
    'product_id' => 'id',
    'name' => 'title',
    'price' => ['source' => 'price', 'transform' => 'float']
]);

$result = $mapper->transformPaginatedResponse(
    $response,
    'data', // Path to items array
    'pagination' // Path to pagination metadata
);

// Result:
// {
//   "data": [transformed items],
//   "meta": pagination info,
//   "transformed_at": "2023-01-15T10:30:00+00:00"
// }
```

### Error Response Handling

```php
$response = Http::get('https://api.example.com/invalid-endpoint');

if (!$response->successful()) {
    $errorData = $mapper->transformErrorResponse($response);
    // Returns structured error data with HTTP status and transformed error body
}
```

## Batch Processing

### Multiple HTTP Responses

```php
$responses = [
    Http::get('https://api.example.com/users/1'),
    Http::get('https://api.example.com/users/2'),
    Http::get('https://api.example.com/users/3')
];

$mapper = HttpDataMapper::create($userMappingRules);

// Transform all responses, skip errors
$results = $mapper->batchTransformResponses($responses, true);

// Result:
// {
//   "successful": [transformed successful responses],
//   "failed": [error details for failed responses],
//   "summary": {"total": 3, "successful": 2, "failed": 1}
// }
```

### Response Collection

```php
$responses = []; // Array of Laravel HTTP Response objects

$mapper = HttpDataMapper::create($rules);
$transformedData = $mapper->transformResponseCollection($responses);
```

## Pipeline Interface

### Fluent Transformation Chain

```php
use Illuminate\Support\Facades\Http;

$response = Http::get('https://api.example.com/products');

$result = $mapper->pipe()
    ->from($response)                    // Start with HTTP response
    ->transform()                        // Apply mapping rules
    ->extract('products')                // Extract specific field
    ->filter(fn($item) => $item['active']) // Filter items
    ->take(10)                          // Take first 10
    ->with(['processed_at' => date('c')]) // Add metadata
    ->toArray();                        // Get result as array
```

### Pipeline Methods

```php
$pipeline = $mapper->pipe();

$result = $pipeline
    ->from($data)                    // HTTP response, array, or JSON string
    ->transform()                    // Apply transformation rules
    ->extract('path.to.data')        // Extract nested data
    ->filter(callable $callback)    // Filter items
    ->map(callable $callback)       // Transform each item
    ->take(int $count)              // Take first N items
    ->skip(int $count)              // Skip first N items
    ->with(array $data)             // Merge additional data
    ->get();                        // Get final result

// Output options
$array = $pipeline->toArray();      // As PHP array
$json = $pipeline->toJson();        // As JSON string
```

## Smart Transform

### Auto-detect Input Type

```php
$mapper = HttpDataMapper::create($rules);

// Works with HTTP responses
$result1 = $mapper->smartTransform($httpResponse);

// Works with PHP arrays
$result2 = $mapper->smartTransform($phpArray);

// Works with JSON strings
$result3 = $mapper->smartTransform($jsonString);
```

## Webhook Processing

### Transform Incoming Webhooks

```php
class WebhookController
{
    public function handle(Request $request)
    {
        $mapper = HttpDataMapper::create([
            'event_type' => 'type',
            'object_id' => 'data.object.id',
            'timestamp' => ['source' => 'created', 'transform' => 'api_timestamp']
        ]);

        $transformedPayload = $mapper->transformWebhookPayload(
            $request->all(),
            $request->headers->all()
        );

        // Process the standardized webhook data
        return response()->json(['status' => 'processed']);
    }
}
```

## Real-World Examples

### E-commerce API Integration

```php
class ShopifyOrderProcessor
{
    private HttpDataMapper $mapper;

    public function __construct()
    {
        $this->mapper = HttpDataMapper::forApiEndpoint('orders', [
            'order_id' => ['source' => 'id', 'transform' => 'string'],
            'order_number' => 'order_number',
            'total_amount' => ['source' => 'total_price', 'transform' => 'api_money'],
            'currency' => 'currency',
            'customer_email' => 'customer.email',
            'created_at' => ['source' => 'created_at', 'transform' => 'api_timestamp'],
            'item_count' => ['source' => 'line_items', 'transform' => 'count_items']
        ])
        ->addArrayMapping('items', 'line_items', [
            'product_id' => ['source' => 'product_id', 'transform' => 'string'],
            'name' => 'name',
            'quantity' => ['source' => 'quantity', 'transform' => 'int'],
            'price' => ['source' => 'price', 'transform' => 'float']
        ])
        ->addTransformer('count_items', fn($items) => array_sum(array_column($items, 'quantity')));
    }

    public function fetchAndProcessOrders(array $orderIds): array
    {
        $responses = [];
        foreach ($orderIds as $orderId) {
            $responses[] = Http::withToken(config('shopify.token'))
                ->get("https://mystore.myshopify.com/admin/api/2023-01/orders/{$orderId}.json");
        }

        return $this->mapper->batchTransformResponses($responses);
    }

    public function processWebhook(Request $request): array
    {
        return $this->mapper->transformWebhookPayload(
            $request->all(),
            $request->headers->all()
        );
    }
}
```

### Multi-API Data Aggregation

```php
class UserDataAggregator
{
    public function aggregateUserData(string $userId): array
    {
        // Fetch from multiple APIs
        $profileResponse = Http::get("https://api1.example.com/users/{$userId}");
        $ordersResponse = Http::get("https://api2.example.com/users/{$userId}/orders");
        $preferencesResponse = Http::get("https://api3.example.com/users/{$userId}/preferences");

        // Different mappers for each API
        $profileMapper = HttpDataMapper::create([
            'id' => 'user_id',
            'name' => 'full_name',
            'email' => 'email_address'
        ]);

        $ordersMapper = HttpDataMapper::create()
            ->addArrayMapping('orders', 'data', [
                'id' => 'order_id',
                'total' => ['source' => 'amount', 'transform' => 'float'],
                'date' => ['source' => 'created_at', 'transform' => 'date_format']
            ]);

        $preferencesMapper = HttpDataMapper::create([
            'newsletter' => ['source' => 'email_newsletter', 'transform' => 'api_boolean'],
            'theme' => 'ui_theme'
        ]);

        // Transform each response
        $profile = $profileMapper->transformResponse($profileResponse);
        $orders = $ordersMapper->transformResponse($ordersResponse);
        $preferences = $preferencesMapper->transformResponse($preferencesResponse);

        // Combine results
        return [
            'user' => $profile,
            'order_history' => $orders['orders'] ?? [],
            'settings' => $preferences,
            'aggregated_at' => date('c')
        ];
    }
}
```

### API Response Caching with Transformation

```php
class CachedApiService
{
    private HttpDataMapper $mapper;

    public function __construct()
    {
        $this->mapper = HttpDataMapper::create([
            'id' => 'product_id',
            'name' => 'title',
            'price' => ['source' => 'price', 'transform' => 'float'],
            'available' => ['source' => 'in_stock', 'transform' => 'api_boolean']
        ]);
    }

    public function getProducts(bool $useCache = true): array
    {
        $cacheKey = 'transformed_products';
        
        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = Http::get('https://api.example.com/products');
        
        $transformed = $this->mapper->pipe()
            ->from($response)
            ->transform()
            ->extract('data')
            ->filter(fn($product) => $product['available'])
            ->with(['cached_at' => date('c')])
            ->toArray();

        Cache::put($cacheKey, $transformed, now()->addMinutes(30));
        
        return $transformed;
    }
}
```

## Built-in API Transformers

### New HTTP-specific Transformers

```php
'api_timestamp'  // Smart timestamp conversion (handles various formats)
'api_boolean'    // Convert string/numeric to boolean ('true', '1', 'yes' → true)
'api_money'      // Smart money conversion (handles cents, decimals)
```

### Usage Examples

```php
$mapper = HttpDataMapper::create([
    'created_at' => ['source' => 'timestamp', 'transform' => 'api_timestamp'],
    'is_active' => ['source' => 'active', 'transform' => 'api_boolean'],
    'price' => ['source' => 'price_cents', 'transform' => 'api_money']
]);

// Input: {"timestamp": 1642248600, "active": "true", "price_cents": "2999"}
// Output: {"created_at": "2022-01-15T10:30:00+00:00", "is_active": true, "price": 29.99}
```

## Performance Considerations

### Efficient Response Processing

```php
// Good: Process response once
$transformedData = $mapper->transformResponse($response);

// Bad: Multiple JSON parsing
$json = $response->body();
$data1 = $mapper->transformJson($json);
$data2 = $mapper->transformJson($json);
```

### Batch Processing Best Practices

```php
// For large response collections, use generators
function processLargeResponseSet(array $responses): \Generator
{
    $mapper = HttpDataMapper::create($rules);
    
    foreach ($responses as $response) {
        if ($response->successful()) {
            yield $mapper->transformResponse($response);
        }
    }
}
```

The HttpDataMapper provides a seamless way to work with Laravel HTTP responses while maintaining all the powerful transformation capabilities of the base JsonDataMapper.