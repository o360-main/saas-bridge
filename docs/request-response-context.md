# Request/Response Context - Processing Tracking System

The Request/Response Context system provides automatic tracking of data processing per Laravel request with connection/record IDs from headers, plus seamless integration with your existing `SaasTokenValidationMiddleware`.

## Features

- **Header-based Context**: Automatically extracts Connection ID, Record ID, and Plugin Record ID from request headers
- **Processing Tracking**: Track SaaS IDs, Sync IDs, Platform IDs, and custom data processing
- **Auto-attach to Response**: Automatically includes processing stats in JSON responses
- **Redis Storage**: Cross-request context persistence for async operations
- **Error Tracking**: Comprehensive error logging per processing type
- **Controller Integration**: Simple trait-based API for controllers

## Quick Start

### 1. Register Middleware

Add to your `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'api' => [
        // ... existing middleware
        \O360Main\SaasBridge\Http\Middleware\RequestContextMiddleware::class,
    ],
];
```

### 2. Use in Controllers

Multiple approaches available - choose your preferred style:

#### Option A: Request Macros (Recommended)
```php
class ProductController extends Controller
{
    public function sync(Request $request)
    {
        // Headers automatically captured: Connection-ID, Record-ID, Plugin-Record-ID
        
        foreach ($products as $product) {
            try {
                $result = $this->syncProduct($product);
                
                // Request macro tracking
                $request->trackSaasId($product->saas_id, true);
                
                if (isset($result['sync_id'])) {
                    $request->trackSyncId($result['sync_id'], true);
                }
                
            } catch (Exception $e) {
                $request->trackSaasId($product->saas_id, false, $e->getMessage());
            }
        }
        
        // Response automatically includes processing stats
        return response()->json(['message' => 'Sync completed']);
    }
}
```

#### Option B: Static Helpers
```php
use O360Main\SaasBridge\Helpers\RequestResponseContext;

class ProductController extends Controller
{
    public function sync(Request $request)
    {
        foreach ($products as $product) {
            try {
                $result = $this->syncProduct($product);
                
                // Static helper tracking
                RequestResponseContext::saasId($product->saas_id, true);
                RequestResponseContext::syncId($result['sync_id'], true);
                
            } catch (Exception $e) {
                RequestResponseContext::saasId($product->saas_id, false, $e->getMessage());
            }
        }
        
        return response()->json(['message' => 'Sync completed']);
    }
}
```

#### Option C: Global Functions
```php
class ProductController extends Controller
{
    public function sync(Request $request)
    {
        foreach ($products as $product) {
            try {
                $result = $this->syncProduct($product);
                
                // Global function tracking
                track_saas_id($product->saas_id, true);
                track_sync_id($result['sync_id'], true);
                
            } catch (Exception $e) {
                track_saas_id($product->saas_id, false, $e->getMessage());
            }
        }
        
        return response()->json(['message' => 'Sync completed']);
    }
}
```

📖 **[Complete Methods Guide](processing-tracking-methods.md)** - All 6 approaches with pros/cons

### 3. Response Output

**Header Mode** (default):
```http
X-Processing-Stats: {
  "connection_id": "conn-123",
  "record_id": "rec-456", 
  "processed_count": 25,
  "duration_ms": 1450.5,
  "success_count": 23,
  "error_count": 2
}
```

**Body Mode**:
```json
{
  "message": "Sync completed",
  "_processing": {
    "request_context": {
      "connection_id": "conn-123",
      "record_id": "rec-456",
      "plugin_record_id": "plugin-789",
      "controller_action": "App\\Http\\Controllers\\ProductController@sync"
    },
    "processing": {
      "total_processed": 25,
      "duration_ms": 1450.5,
      "success_count": 23,
      "error_count": 2
    },
    "tracking_data": {
      "saas_ids": [...],
      "sync_ids": [...]
    },
    "errors": [...]
  }
}
```

## Configuration

### Environment Variables

```env
# Request Context Configuration
SAAS_CONTEXT_AUTO_ATTACH=true
SAAS_CONTEXT_ATTACH_MODE=header
SAAS_CONTEXT_REDIS_TTL=3600
SAAS_CONTEXT_KEY_PREFIX=request_context
SAAS_CONTEXT_ENABLED=true
SAAS_CONTEXT_LOG_STATS=false
```

### Config File

```php
'request_context' => [
    'headers' => [
        'connection_id' => ['X-Connection-ID', 'Connection-ID', 'connection-id'],
        'record_id' => ['X-Record-ID', 'Record-ID', 'record-id'],
        'plugin_record_id' => ['X-Plugin-Record-ID', 'Plugin-Record-ID', 'plugin-record-id'],
    ],
    'auto_attach' => env('SAAS_CONTEXT_AUTO_ATTACH', true),
    'attach_mode' => env('SAAS_CONTEXT_ATTACH_MODE', 'header'), // 'header', 'body', 'both'
    'redis_ttl' => env('SAAS_CONTEXT_REDIS_TTL', 3600),
    'enabled' => env('SAAS_CONTEXT_ENABLED', true),
],
```

## Tracking Methods

### Basic Tracking

```php
// Track SaaS ID processing
$this->trackSaasId('saas_123', true); // success
$this->trackSaasId('saas_456', false, 'Validation failed'); // with error

// Track Sync ID processing  
$this->trackSyncId('sync_789', true);

// Track Platform ID processing
$this->trackPlatformId('platform_101', true);
```

### Advanced Tracking

```php
// Track custom data types
$this->trackCustom('product', 'prod_123', true, null, [
    'sku' => 'ABC123',
    'price' => 29.99
]);

// Track model operations
$this->trackModelOperation('create', $product, true);
$this->trackModelOperation('update', $user, false, 'Validation error');

// Batch tracking
$this->trackBatch('products', [
    ['id' => 'prod_1', 'success' => true],
    ['id' => 'prod_2', 'success' => false, 'error' => 'Out of stock']
]);
```

### API Response Tracking

```php
// Auto-detect sync_id from API response
$response = Http::post('/api/sync', $data);
$result = $response->json();

$this->trackApiResponse($result); // Automatically tracks sync_id if present
```

### Processing Stats

```php
// Get current processing stats
$stats = $this->getProcessingStats();

// Check for errors
if ($this->hasProcessingErrors()) {
    $errors = $this->getProcessingErrors();
}

// Get context IDs
$connectionId = $this->getConnectionId();
$recordId = $this->getRecordId();
$pluginRecordId = $this->getPluginRecordId();
```

## Integration with SaasTokenValidationMiddleware

The system works seamlessly with your existing middleware:

```php
// Your existing middleware handles token validation
// RequestContextMiddleware captures the headers after validation

protected $middlewareGroups = [
    'api' => [
        \O360Main\SaasBridge\Http\Middleware\SaasTokenValidationMiddleware::class,
        \O360Main\SaasBridge\Http\Middleware\RequestContextMiddleware::class,
    ],
];
```

## Advanced Usage

### Manual Context Management

```php
use O360Main\SaasBridge\Helpers\RequestResponseContext;

// Get singleton instance
$context = RequestResponseContext::getInstance();

// Or use static helper
$context = RequestResponseContext::track();

// Manual initialization (if not using middleware)
$context->initFromRequest($request);
```

### Background Job Integration

```php
class ProcessDataJob implements ShouldQueue
{
    public function handle()
    {
        // Load context from Redis using connection ID
        $context = RequestResponseContext::loadFromRedis($this->connectionId);
        
        if ($context) {
            // Continue tracking in background job
            $context->trackCustom('background_job', $this->jobId, true);
        }
    }
}
```

### Custom Response Attachment

```php
public function customResponse(Request $request)
{
    // ... processing logic
    
    $response = response()->json($data);
    
    // Manually attach processing stats
    if ($request->header('X-Include-Stats')) {
        RequestResponseContext::getInstance()->attachToResponse($response);
    }
    
    return $response;
}
```

## Redis Storage

Context data is automatically stored in Redis for:
- Cross-request persistence
- Background job access
- Async operation tracking

**Redis Key Format**: `request_context:{connection_id}`

**Stored Data**:
```json
{
  "connection_id": "conn-123",
  "record_id": "rec-456",
  "plugin_record_id": "plugin-789",
  "controller_action": "App\\Http\\Controllers\\ProductController@sync",
  "start_time": 1634567890.123,
  "tracking_data": {
    "saas_ids": [...],
    "sync_ids": [...],
    "custom": {...}
  },
  "processed_count": 25,
  "errors": [...],
  "updated_at": "2023-10-18T10:30:45Z"
}
```

## Response Modes

### Header Mode (Default)
Processing stats attached as `X-Processing-Stats` header:

```http
X-Processing-Stats: {"connection_id":"conn-123","processed_count":25,"duration_ms":1450.5}
```

### Body Mode
Processing stats included in response body:

```json
{
  "data": {...},
  "_processing": {...}
}
```

### Both Mode
Stats included in both header and body.

## Error Handling

```php
try {
    $result = $this->processData($data);
    $this->trackSaasId($data['saas_id'], true);
} catch (ValidationException $e) {
    $this->trackSaasId($data['saas_id'], false, 'Validation: ' . $e->getMessage());
    $this->addTrackingError('validation', $e->getMessage(), $data['saas_id']);
} catch (Exception $e) {
    $this->trackSaasId($data['saas_id'], false, $e->getMessage());
    $this->addTrackingError('general', $e->getMessage());
}
```

## Monitoring & Debugging

### Enable Logging

```env
SAAS_CONTEXT_LOG_STATS=true
SAAS_CONTEXT_LOG_CHANNEL=processing
```

### Custom Logging

```php
// Log processing stats to custom channel
Log::channel('processing')->info('Request completed', $this->getProcessingStats());
```

### Health Checks

```php
public function health()
{
    $stats = RequestResponseContext::getInstance()->getProcessingStats();
    
    return response()->json([
        'status' => 'ok',
        'processing' => $stats['processing']
    ]);
}
```

## Best Practices

### Controller Usage

```php
class SyncController extends Controller
{
    use TracksProcessing;
    
    public function syncProducts(Request $request)
    {
        $products = $request->input('products', []);
        
        foreach ($products as $productData) {
            try {
                // Process product
                $product = $this->createOrUpdateProduct($productData);
                
                // Track success
                $this->trackSaasId($productData['saas_id'], true);
                $this->trackModelOperation('sync', $product, true);
                
                // Track API response if making external calls
                $apiResponse = $this->callExternalApi($productData);
                $this->trackApiResponse($apiResponse['data'] ?? []);
                
            } catch (Exception $e) {
                $this->trackSaasId($productData['saas_id'], false, $e->getMessage());
                $this->trackModelOperation('sync', Product::class, false, $e->getMessage());
            }
        }
        
        return response()->json([
            'message' => 'Sync completed',
            'processed' => $this->context()->getProcessedCount()
        ]);
    }
}
```

### Service Class Integration

```php
class ProductSyncService
{
    use TracksProcessing;
    
    public function syncFromApi(array $apiData)
    {
        foreach ($apiData as $item) {
            $success = $this->processItem($item);
            
            $this->trackCustom('api_import', $item['id'], $success, 
                $success ? null : 'Processing failed');
        }
        
        return $this->getProcessingStats();
    }
}
```

## Troubleshooting

### Context Not Initialized
```php
// Check if context has required IDs
if (!$this->getConnectionId()) {
    Log::warning('Connection ID not found in request headers');
}
```

### Redis Connection Issues
```php
// Test Redis connectivity
try {
    RequestResponseContext::getInstance()->trackCustom('test', 'test', true);
} catch (Exception $e) {
    Log::error('Redis context storage failed: ' . $e->getMessage());
}
```

### Missing Processing Stats
- Ensure `RequestContextMiddleware` is registered
- Check `SAAS_CONTEXT_AUTO_ATTACH=true`
- Verify response is `JsonResponse` instance

This system provides comprehensive request/response tracking while maintaining simplicity and performance!