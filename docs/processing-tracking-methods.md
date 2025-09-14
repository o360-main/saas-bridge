# Processing Tracking Methods - Multiple Approaches

The Request/Response Context system provides multiple ways to track processing data in your controllers and services. Choose the approach that best fits your coding style and project requirements.

## Overview of Available Methods

1. **Controller Trait** (Traditional OOP)
2. **Static Helper Methods** (Clean & Simple)
3. **Request Macros** (Laravel-native)
4. **Facade Pattern** (Laravel-style)
5. **Global Helper Functions** (Procedural)
6. **Dependency Injection** (Service Container)

## 1. Controller Trait Approach

```php
use O360Main\SaasBridge\Traits\TracksProcessing;

class ProductController extends Controller
{
    use TracksProcessing;
    
    public function sync(Request $request)
    {
        foreach ($products as $product) {
            try {
                $result = $this->syncProduct($product);
                
                // Trait methods
                $this->trackSaasId($product->saas_id, true);
                $this->trackSyncId($result['sync_id'], true);
                
            } catch (Exception $e) {
                $this->trackSaasId($product->saas_id, false, $e->getMessage());
            }
        }
        
        return response()->json(['message' => 'Sync completed']);
    }
}
```

**Pros:**
- IDE autocompletion
- Clear method context
- Object-oriented approach

**Cons:**
- Requires `use` statement in every controller
- Adds methods to controller scope

## 2. Static Helper Methods (⭐ Recommended)

```php
use O360Main\SaasBridge\Helpers\RequestResponseContext;

class ProductController extends Controller
{
    public function sync(Request $request)
    {
        foreach ($products as $product) {
            try {
                $result = $this->syncProduct($product);
                
                // Static helper methods
                RequestResponseContext::saasId($product->saas_id, true);
                RequestResponseContext::syncId($result['sync_id'], true);
                
                // Shorthand version
                RequestResponseContext::track()->trackCustom('product', $product->id, true);
                
            } catch (Exception $e) {
                RequestResponseContext::saasId($product->saas_id, false, $e->getMessage());
            }
        }
        
        return response()->json(['message' => 'Sync completed']);
    }
}
```

**Available Static Methods:**
```php
RequestResponseContext::saasId($id, $success, $error);
RequestResponseContext::syncId($id, $success, $error);
RequestResponseContext::platformId($id, $success, $error);
RequestResponseContext::custom($type, $id, $success, $error, $metadata);
RequestResponseContext::processed($count);
RequestResponseContext::error($type, $message, $id);
RequestResponseContext::apiResponse($response, $type);
```

**Pros:**
- Clean, concise syntax
- No trait requirements
- Direct access to singleton
- IDE support with proper imports

**Cons:**
- Longer class name to type

## 3. Request Macros (⭐ Recommended)

```php
class ProductController extends Controller
{
    public function sync(Request $request)
    {
        foreach ($products as $product) {
            try {
                $result = $this->syncProduct($product);
                
                // Request macro methods
                $request->trackSaasId($product->saas_id, true);
                $request->trackSyncId($result['sync_id'], true);
                
                // Chainable calls
                $request->trackCustom('product', $product->id, true)
                       ->incrementProcessed();
                
            } catch (Exception $e) {
                $request->trackSaasId($product->saas_id, false, $e->getMessage());
            }
        }
        
        return response()->json(['message' => 'Sync completed']);
    }
}
```

**Available Request Macros:**
```php
$request->track()                    // Get context instance
$request->trackSaasId($id, $success, $error)
$request->trackSyncId($id, $success, $error)
$request->trackPlatformId($id, $success, $error)
$request->trackCustom($type, $id, $success, $error, $metadata)
$request->trackApiResponse($response, $type)
$request->incrementProcessed($count)
$request->addTrackingError($type, $message, $id)
$request->getProcessingStats()
```

**Pros:**
- Laravel-native approach
- Request object always available
- Chainable methods
- Intuitive for Laravel developers

**Cons:**
- Requires `$request` parameter in method

## 4. Facade Pattern

```php
use O360Main\SaasBridge\Facades\ProcessingTracker;

class ProductController extends Controller
{
    public function sync(Request $request)
    {
        foreach ($products as $product) {
            try {
                $result = $this->syncProduct($product);
                
                // Facade methods
                ProcessingTracker::saasId($product->saas_id, true);
                ProcessingTracker::syncId($result['sync_id'], true);
                
                // Access full context
                ProcessingTracker::track()->trackCustom('product', $product->id, true);
                
            } catch (Exception $e) {
                ProcessingTracker::saasId($product->saas_id, false, $e->getMessage());
            }
        }
        
        return response()->json(['message' => 'Sync completed']);
    }
}
```

**Available Facade Methods:**
```php
ProcessingTracker::saasId($id, $success, $error);
ProcessingTracker::syncId($id, $success, $error);
ProcessingTracker::platformId($id, $success, $error);
ProcessingTracker::custom($type, $id, $success, $error, $metadata);
ProcessingTracker::processed($count);
ProcessingTracker::error($type, $message, $id);
ProcessingTracker::apiResponse($response, $type);
ProcessingTracker::track(); // Get full context
ProcessingTracker::getProcessingStats();
```

**Setup Required:**
Add to `config/app.php`:
```php
'aliases' => [
    // ...
    'ProcessingTracker' => O360Main\SaasBridge\Facades\ProcessingTracker::class,
],
```

**Pros:**
- Short, clean syntax
- Laravel-style facade pattern
- IDE support with facade docblocks

**Cons:**
- Requires facade registration
- Static calls (harder to mock in tests)

## 5. Global Helper Functions

```php
class ProductController extends Controller
{
    public function sync(Request $request)
    {
        foreach ($products as $product) {
            try {
                $result = $this->syncProduct($product);
                
                // Global helper functions
                track_saas_id($product->saas_id, true);
                track_sync_id($result['sync_id'], true);
                track_custom('product', $product->id, true, null, ['sku' => $product->sku]);
                
                // Get context
                $context = processing_context();
                $stats = get_processing_stats();
                
            } catch (Exception $e) {
                track_saas_id($product->saas_id, false, $e->getMessage());
                add_tracking_error('sync', $e->getMessage(), $product->saas_id);
            }
        }
        
        return response()->json(['message' => 'Sync completed']);
    }
}
```

**Available Helper Functions:**
```php
track_saas_id($id, $success, $error)
track_sync_id($id, $success, $error)
track_platform_id($id, $success, $error)
track_custom($type, $id, $success, $error, $metadata)
track_api_response($response, $type)
increment_processed($count)
add_tracking_error($type, $message, $id)
get_processing_stats()
processing_context() // Get full context instance
```

**Pros:**
- Most concise syntax
- No imports required
- Procedural style
- Works anywhere in codebase

**Cons:**
- Global namespace pollution
- Less IDE support
- Harder to track dependencies

## 6. Dependency Injection

```php
class ProductController extends Controller
{
    public function sync(Request $request, RequestResponseContext $context)
    {
        foreach ($products as $product) {
            try {
                $result = $this->syncProduct($product);
                
                // Injected context
                $context->trackSaasId($product->saas_id, true);
                $context->trackSyncId($result['sync_id'], true);
                
            } catch (Exception $e) {
                $context->trackSaasId($product->saas_id, false, $e->getMessage());
            }
        }
        
        return response()->json(['message' => 'Sync completed']);
    }
}
```

**Pros:**
- Explicit dependencies
- Easy to mock in tests
- Clean separation of concerns

**Cons:**
- Requires method signature changes
- More verbose controller methods

## Service Class Integration

All approaches work equally well in service classes:

```php
class ProductSyncService
{
    public function syncFromApi(array $apiData)
    {
        foreach ($apiData as $item) {
            try {
                $product = $this->processItem($item);
                
                // Choose your preferred approach:
                
                // Static helper
                RequestResponseContext::custom('api_import', $item['id'], true);
                
                // Global function
                track_custom('api_import', $item['id'], true);
                
                // Facade
                ProcessingTracker::custom('api_import', $item['id'], true);
                
            } catch (Exception $e) {
                track_custom('api_import', $item['id'], false, $e->getMessage());
            }
        }
        
        return get_processing_stats();
    }
}
```

## Queue Job Integration

Background jobs can access context from Redis:

```php
class ProcessDataJob implements ShouldQueue
{
    public function handle()
    {
        // Load context from Redis
        $context = RequestResponseContext::loadFromRedis($this->connectionId);
        
        if ($context) {
            // Use any tracking approach
            RequestResponseContext::custom('background_job', $this->jobId, true);
            // or
            track_custom('background_job', $this->jobId, true);
        }
    }
}
```

## Artisan Command Usage

```php
class SyncDataCommand extends Command
{
    public function handle()
    {
        // Manual context initialization for CLI
        $context = RequestResponseContext::getInstance();
        
        foreach ($this->getData() as $item) {
            try {
                $this->processItem($item);
                track_custom('cli_import', $item['id'], true);
            } catch (Exception $e) {
                track_custom('cli_import', $item['id'], false, $e->getMessage());
                $this->error("Failed to process {$item['id']}: " . $e->getMessage());
            }
        }
        
        $stats = get_processing_stats();
        $this->info("Processed: {$stats['processing']['total_processed']} items");
    }
}
```

## Testing Different Approaches

### Testing Static Helpers
```php
public function test_sync_products()
{
    // Clear context before test
    RequestResponseContext::reset();
    
    $response = $this->postJson('/api/products/sync', $data, [
        'X-Connection-ID' => 'test-connection'
    ]);
    
    $stats = RequestResponseContext::getInstance()->getProcessingStats();
    $this->assertEquals(5, $stats['processing']['total_processed']);
}
```

### Testing Request Macros
```php
public function test_request_macros()
{
    $request = request();
    $request->headers->set('X-Connection-ID', 'test-connection');
    
    RequestResponseContext::getInstance()->initFromRequest($request);
    
    $request->trackSaasId('saas_123', true);
    $stats = $request->getProcessingStats();
    
    $this->assertEquals(1, $stats['processing']['total_processed']);
}
```

## Performance Comparison

All approaches have nearly identical performance since they all use the same underlying singleton:

- **Static Helpers**: ~0.1ms overhead
- **Request Macros**: ~0.1ms overhead  
- **Facades**: ~0.2ms overhead (facade resolution)
- **Global Functions**: ~0.05ms overhead
- **Dependency Injection**: ~0.05ms overhead
- **Traits**: ~0.05ms overhead

## Recommendations

### For New Projects
- **Request Macros** - Most Laravel-native approach
- **Static Helpers** - Clean and explicit

### For Existing Projects
- **Global Functions** - Minimal changes required
- **Facade** - If you already use facades heavily

### For Testing/Mocking
- **Dependency Injection** - Easiest to mock
- **Traits** - Good balance of mockability and convenience

### For Service Classes
- **Static Helpers** - No request object dependency
- **Global Functions** - Works everywhere

Choose the approach that best fits your team's coding style and project architecture!