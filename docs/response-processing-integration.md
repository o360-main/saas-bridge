# Response Processing Integration - TriggerResponse, ImportResponse, ExportResponse

This guide shows how processing tracking automatically integrates with your existing response classes (`TriggerResponse`, `ImportResponse`, `ExportResponse`) using the corrected headers: `connection_id`, `record_id`, and `record_log_id`.

## Corrected Headers

The system now uses the correct header names:
- `X-Connection-ID` / `Connection-ID` / `connection-id`
- `X-Record-ID` / `Record-ID` / `record-id`  
- `X-Record-Log-ID` / `Record-Log-ID` / `record-log-id`

## Automatic Integration with Existing Responses

Your existing response classes now automatically include processing stats without any code changes needed in your controllers.

### TriggerResponse with Processing Stats

**Controller Example:**
```php
class WebhookController extends Controller
{
    public function trigger(Request $request)
    {
        $data = $request->all();
        $processed = 0;
        $errors = [];
        
        foreach ($data['items'] ?? [] as $item) {
            try {
                // Process webhook item
                $result = $this->processWebhookItem($item);
                
                // Track processing
                $request->trackSaasId($item['saas_id'], true);
                
                if (isset($result['sync_id'])) {
                    $request->trackSyncId($result['sync_id'], true);
                }
                
                $processed++;
                
            } catch (Exception $e) {
                $request->trackSaasId($item['saas_id'], false, $e->getMessage());
                $errors[] = $e->getMessage();
            }
        }
        
        $isCompleted = $processed === count($data['items'] ?? []);
        $hasErrors = count($errors) > 0;
        
        return new TriggerResponse(
            is_completed: $isCompleted,
            is_error: $hasErrors,
            progress_in_percentage: $isCompleted ? 100 : ($processed / count($data['items']) * 100),
            interval_in_seconds: $hasErrors ? 30 : null,
            error_message: $hasErrors ? implode('; ', $errors) : null,
            data: [
                'processed_count' => $processed,
                'total_items' => count($data['items'] ?? []),
                'errors' => $errors
            ]
        );
    }
}
```

**Request:**
```http
POST /api/webhook/trigger
Headers:
  X-Connection-ID: conn-webhook-123
  X-Record-ID: rec-webhook-456
  X-Record-Log-ID: log-webhook-789
  Content-Type: application/json

{
  "items": [
    {"saas_id": "saas_001", "type": "product", "action": "update"},
    {"saas_id": "saas_002", "type": "order", "action": "create"},
    {"saas_id": "saas_003", "type": "customer", "action": "update"}
  ]
}
```

**Generated Response (Header Mode):**
```http
HTTP/1.1 200 OK
Content-Type: application/json
X-Processing-Stats: {"connection_id":"conn-webhook-123","record_id":"rec-webhook-456","record_log_id":"log-webhook-789","processed_count":3,"duration_ms":245.7,"success_count":3,"error_count":0}

{
  "is_completed": true,
  "progress_in_percentage": 100,
  "interval_in_seconds": null,
  "is_error": false,
  "error_message": null,
  "data": {
    "processed_count": 3,
    "total_items": 3,
    "errors": []
  }
}
```

**Generated Response (Body Mode):**
```json
{
  "is_completed": true,
  "progress_in_percentage": 100,
  "interval_in_seconds": null,
  "is_error": false,
  "error_message": null,
  "data": {
    "processed_count": 3,
    "total_items": 3,
    "errors": []
  },
  "_processing": {
    "request_context": {
      "connection_id": "conn-webhook-123",
      "record_id": "rec-webhook-456",
      "record_log_id": "log-webhook-789",
      "controller_action": "App\\Http\\Controllers\\WebhookController@trigger"
    },
    "processing": {
      "total_processed": 3,
      "duration_ms": 245.7,
      "success_count": 3,
      "error_count": 0
    },
    "tracking_data": {
      "saas_ids": [
        {
          "id": "saas_001",
          "success": true,
          "error": null,
          "timestamp": "2023-10-18T10:30:45.123Z"
        },
        {
          "id": "saas_002", 
          "success": true,
          "error": null,
          "timestamp": "2023-10-18T10:30:45.234Z"
        },
        {
          "id": "saas_003",
          "success": true,
          "error": null,
          "timestamp": "2023-10-18T10:30:45.345Z"
        }
      ],
      "sync_ids": [...]
    },
    "errors": []
  }
}
```

### ImportResponse with Processing Stats

**Controller Example:**
```php
class DataController extends Controller
{
    public function import(Request $request)
    {
        $importData = $request->input('data', []);
        $successCount = 0;
        $errorMessages = [];
        
        foreach ($importData as $item) {
            try {
                // Import item to database
                $model = $this->importItem($item);
                
                // Track successful import
                $request->trackSaasId($item['saas_id'], true);
                $request->trackCustom('import', $model->id, true, null, [
                    'type' => $item['type'] ?? 'unknown',
                    'model_class' => get_class($model)
                ]);
                
                $successCount++;
                
            } catch (Exception $e) {
                $request->trackSaasId($item['saas_id'], false, $e->getMessage());
                $request->trackCustom('import', $item['saas_id'], false, $e->getMessage());
                $errorMessages[] = $e->getMessage();
            }
        }
        
        $success = $successCount > 0 && count($errorMessages) === 0;
        
        return new ImportResponse(
            success: $success,
            message: $success 
                ? "Successfully imported {$successCount} items"
                : "Import completed with errors: " . implode('; ', $errorMessages),
            data: [
                'imported_count' => $successCount,
                'total_count' => count($importData),
                'success_rate' => count($importData) > 0 ? ($successCount / count($importData)) * 100 : 0,
                'errors' => $errorMessages
            ]
        );
    }
}
```

**Generated Response:**
```http
HTTP/1.1 200 OK
X-Processing-Stats: {"connection_id":"conn-import-123","record_id":"rec-import-456","record_log_id":"log-import-789","processed_count":15,"duration_ms":1247.8,"success_count":13,"error_count":2}

{
  "data": {
    "imported_count": 13,
    "total_count": 15,
    "success_rate": 86.67,
    "errors": [
      "Validation failed for item saas_014",
      "Duplicate key constraint for item saas_015"
    ]
  },
  "success": false,
  "message": "Import completed with errors: Validation failed for item saas_014; Duplicate key constraint for item saas_015"
}
```

### ExportResponse with Processing Stats

**Controller Example:**
```php
class DataController extends Controller
{
    public function export(Request $request)
    {
        $filters = $request->input('filters', []);
        $exportedData = [];
        $processedCount = 0;
        
        // Get data to export
        $dataToExport = $this->getDataForExport($filters);
        
        foreach ($dataToExport as $item) {
            try {
                // Transform item for export
                $exportedItem = $this->transformForExport($item);
                $exportedData[] = $exportedItem;
                
                // Track export processing
                $request->trackSaasId($item->saas_id, true);
                $request->trackCustom('export', $item->id, true, null, [
                    'export_format' => $request->input('format', 'json'),
                    'item_type' => $item->type
                ]);
                
                $processedCount++;
                
            } catch (Exception $e) {
                $request->trackSaasId($item->saas_id, false, $e->getMessage());
                $request->trackCustom('export', $item->id, false, $e->getMessage());
            }
        }
        
        $success = $processedCount === count($dataToExport);
        
        return new ExportResponse(
            success: $success,
            message: $success 
                ? "Successfully exported {$processedCount} items"
                : "Export completed with some failures",
            data: [
                'exported_items' => $exportedData,
                'export_metadata' => [
                    'total_processed' => $processedCount,
                    'total_available' => count($dataToExport),
                    'export_format' => $request->input('format', 'json'),
                    'filters_applied' => $filters,
                    'exported_at' => now()->toISOString()
                ]
            ]
        );
    }
}
```

## Enhanced Response Classes

For more control over processing data inclusion, use the enhanced response classes:

### EnhancedImportResponse with Processing Summary

```php
class DataController extends Controller
{
    public function importWithStats(Request $request)
    {
        // ... processing logic with tracking ...
        
        return new EnhancedImportResponse(
            success: $success,
            message: $message,
            data: $data,
            include_processing_summary: true, // Include summary in response data
            include_processing_details: false // Don't include full details
        );
    }
}
```

**Response with Processing Summary:**
```json
{
  "data": {
    "imported_count": 25,
    "items": [...]
  },
  "success": true,
  "message": "Successfully imported 25 items",
  "processing_summary": {
    "connection_id": "conn-import-123",
    "record_id": "rec-import-456", 
    "record_log_id": "log-import-789",
    "processed_count": 25,
    "success_count": 25,
    "error_count": 0,
    "duration_ms": 1456.7,
    "has_errors": false
  }
}
```

### EnhancedTriggerResponse with Processing Data

```php
return new EnhancedTriggerResponse(
    is_completed: true,
    progress_in_percentage: 100,
    interval_in_seconds: null,
    is_error: false,
    error_message: null,
    data: $responseData,
    include_processing_in_data: true // Include processing summary
);
```

## Progressive/Async Operations

For long-running operations, track progress over multiple requests:

**Initial Trigger:**
```php
public function startAsyncOperation(Request $request)
{
    $jobId = 'async_job_' . time();
    
    // Track job initiation
    $request->trackCustom('job_started', $jobId, true, null, [
        'job_type' => 'data_sync',
        'estimated_items' => $request->input('item_count', 0)
    ]);
    
    // Dispatch background job
    ProcessLargeDatasetJob::dispatch($jobId, $request->track()->getConnectionId());
    
    return new TriggerResponse(
        is_completed: false,
        progress_in_percentage: 0,
        interval_in_seconds: 5, // Check back in 5 seconds
        data: ['job_id' => $jobId]
    );
}
```

**Progress Check:**
```php
public function checkProgress(Request $request, string $jobId)
{
    // Load context from Redis
    $context = RequestResponseContext::loadFromRedis($request->track()->getConnectionId());
    
    if (!$context) {
        return new TriggerResponse(
            is_completed: false,
            is_error: true,
            error_message: 'Job context not found'
        );
    }
    
    $stats = $context->getProcessingStats();
    $processed = $stats['processing']['total_processed'];
    $estimated = 1000; // Get from job metadata
    
    $isCompleted = $processed >= $estimated;
    $progress = $estimated > 0 ? min(100, ($processed / $estimated) * 100) : 0;
    
    return new TriggerResponse(
        is_completed: $isCompleted,
        progress_in_percentage: (int) $progress,
        interval_in_seconds: $isCompleted ? null : 5,
        data: [
            'job_id' => $jobId,
            'processed_count' => $processed,
            'estimated_total' => $estimated
        ]
    );
}
```

## Configuration Options

Control processing stats attachment:

```env
# Enable/disable processing stats
SAAS_CONTEXT_ENABLED=true
SAAS_CONTEXT_AUTO_ATTACH=true

# Attachment mode: header, body, or both
SAAS_CONTEXT_ATTACH_MODE=header

# Redis storage settings
SAAS_CONTEXT_REDIS_TTL=3600
SAAS_CONTEXT_KEY_PREFIX=request_context
```

## Key Benefits

✅ **Zero Controller Changes** - Existing controllers work unchanged  
✅ **Automatic Stats** - Processing data attached to all responses  
✅ **Flexible Modes** - Header, body, or both attachment options  
✅ **Progressive Tracking** - Perfect for long-running operations  
✅ **Error Correlation** - Link errors to specific IDs and contexts  
✅ **Redis Persistence** - Background job integration  
✅ **Multiple Approaches** - Choose your preferred tracking method  

Your existing `TriggerResponse`, `ImportResponse`, and `ExportResponse` classes now provide rich processing insights without any changes to your controller logic!