# Merged Response Examples - Enhanced Features in Existing Classes

The processing tracking features have been seamlessly merged into your existing response classes without changing their structure. All existing code works unchanged, with optional enhanced features available through new constructor parameters.

## TriggerResponse - Enhanced with Processing

### Basic Usage (No Changes Required)
```php
// Existing code works unchanged
return new TriggerResponse(
    is_completed: true,
    progress_in_percentage: 100,
    data: ['processed_items' => 25]
);
```

**Response (Header Mode):**
```http
HTTP/1.1 200 OK
X-Processing-Stats: {"connection_id":"conn-123","record_id":"rec-456","record_log_id":"log-789","processed_count":25,"duration_ms":1247.8,"success_count":25,"error_count":0}

{
  "is_completed": true,
  "progress_in_percentage": 100,
  "interval_in_seconds": null,
  "is_error": false,
  "error_message": null,
  "data": {
    "processed_items": 25
  }
}
```

### Enhanced Usage (Optional Processing Summary)
```php
// Enhanced with processing summary in response body
return new TriggerResponse(
    is_completed: true,
    progress_in_percentage: 100,
    data: ['processed_items' => 25],
    include_processing_in_data: true // New optional parameter
);
```

**Response with Processing Summary:**
```json
{
  "is_completed": true,
  "progress_in_percentage": 100,
  "interval_in_seconds": null,
  "is_error": false,
  "error_message": null,
  "data": {
    "processed_items": 25
  },
  "processing_summary": {
    "connection_id": "conn-123",
    "record_id": "rec-456",
    "record_log_id": "log-789",
    "processed_count": 25,
    "success_count": 25,
    "error_count": 0,
    "duration_ms": 1247.8,
    "has_errors": false
  }
}
```

## ImportResponse - Enhanced with Processing

### Basic Usage (No Changes Required)
```php
// Existing code works unchanged
return new ImportResponse(
    success: true,
    message: "Successfully imported 15 products",
    data: ['imported_products' => $products]
);
```

### Enhanced Usage (Optional Processing Details)
```php
// Enhanced with processing summary
return new ImportResponse(
    success: true,
    message: "Successfully imported 15 products",
    data: ['imported_products' => $products],
    include_processing_summary: true // New optional parameter
);

// Or with full processing details
return new ImportResponse(
    success: true,
    message: "Successfully imported 15 products", 
    data: ['imported_products' => $products],
    include_processing_summary: true,
    include_processing_details: true // New optional parameter
);
```

**Response with Processing Summary:**
```json
{
  "data": {
    "imported_products": [...]
  },
  "success": true,
  "message": "Successfully imported 15 products",
  "processing_summary": {
    "connection_id": "conn-123",
    "record_id": "rec-456",
    "record_log_id": "log-789",
    "processed_count": 15,
    "success_count": 15,
    "error_count": 0,
    "duration_ms": 892.3,
    "has_errors": false
  }
}
```

**Response with Full Processing Details:**
```json
{
  "data": {
    "imported_products": [...]
  },
  "success": true,
  "message": "Successfully imported 15 products",
  "processing_summary": {
    "connection_id": "conn-123",
    "record_id": "rec-456",
    "record_log_id": "log-789",
    "processed_count": 15,
    "success_count": 15,
    "error_count": 0,
    "duration_ms": 892.3,
    "has_errors": false
  },
  "processing_details": {
    "request_context": {
      "connection_id": "conn-123",
      "record_id": "rec-456",
      "record_log_id": "log-789",
      "controller_action": "App\\Http\\Controllers\\DataController@import"
    },
    "processing": {
      "total_processed": 15,
      "duration_ms": 892.3,
      "start_time": "2023-10-18T10:30:45.123Z",
      "end_time": "2023-10-18T10:30:46.015Z",
      "success_count": 15,
      "error_count": 0
    },
    "tracking_data": {
      "saas_ids": [
        {
          "id": "saas_001",
          "success": true,
          "error": null,
          "timestamp": "2023-10-18T10:30:45.234Z"
        }
        // ... more tracking data
      ],
      "custom": {
        "import": [
          {
            "id": "prod_001",
            "success": true,
            "error": null,
            "metadata": {
              "type": "product",
              "model_class": "App\\Models\\Product"
            },
            "timestamp": "2023-10-18T10:30:45.345Z"
          }
          // ... more custom tracking
        ]
      }
    },
    "errors": []
  }
}
```

## ExportResponse - Enhanced with Processing

### Basic Usage (No Changes Required)
```php
// Existing code works unchanged
return new ExportResponse(
    success: true,
    message: "Export completed successfully",
    data: ['exported_data' => $exportedData]
);
```

### Enhanced Usage (Optional Processing Summary)
```php
// Enhanced with processing summary
return new ExportResponse(
    success: true,
    message: "Export completed successfully",
    data: ['exported_data' => $exportedData],
    include_processing_summary: true // New optional parameter
);
```

## Real-World Controller Examples

### Import Controller with Enhanced Tracking

```php
class ProductImportController extends Controller
{
    public function import(Request $request)
    {
        $products = $request->input('products', []);
        $imported = [];
        $errors = [];
        
        foreach ($products as $productData) {
            try {
                // Import product
                $product = Product::create($productData);
                $imported[] = $product;
                
                // Track successful import
                $request->trackSaasId($productData['saas_id'], true);
                $request->trackCustom('product_import', $product->id, true, null, [
                    'sku' => $product->sku,
                    'category' => $product->category
                ]);
                
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
                $request->trackSaasId($productData['saas_id'], false, $e->getMessage());
            }
        }
        
        $success = count($errors) === 0;
        
        // Basic response (existing structure)
        if (!$request->has('include_processing')) {
            return new ImportResponse(
                success: $success,
                message: $success 
                    ? "Successfully imported " . count($imported) . " products"
                    : "Import completed with errors",
                data: [
                    'imported_count' => count($imported),
                    'error_count' => count($errors),
                    'imported_products' => $imported
                ]
            );
        }
        
        // Enhanced response with processing details
        return new ImportResponse(
            success: $success,
            message: $success 
                ? "Successfully imported " . count($imported) . " products"
                : "Import completed with errors",
            data: [
                'imported_count' => count($imported),
                'error_count' => count($errors),
                'imported_products' => $imported
            ],
            include_processing_summary: $request->boolean('include_processing_summary', true),
            include_processing_details: $request->boolean('include_processing_details', false)
        );
    }
}
```

### Async Trigger Controller

```php
class WebhookTriggerController extends Controller
{
    public function trigger(Request $request)
    {
        $webhookData = $request->all();
        $jobId = 'webhook_job_' . time();
        
        // Process webhook items
        $processedCount = 0;
        $totalItems = count($webhookData['items'] ?? []);
        
        foreach ($webhookData['items'] ?? [] as $item) {
            try {
                // Process webhook item
                $result = $this->processWebhookItem($item);
                
                // Track processing
                $request->trackSaasId($item['saas_id'], true);
                
                if (isset($result['sync_id'])) {
                    $request->trackSyncId($result['sync_id'], true);
                }
                
                $request->trackCustom('webhook_processing', $item['id'], true, null, [
                    'webhook_type' => $item['type'] ?? 'unknown',
                    'processing_time' => microtime(true) - $start
                ]);
                
                $processedCount++;
                
            } catch (Exception $e) {
                $request->trackSaasId($item['saas_id'], false, $e->getMessage());
                $request->trackCustom('webhook_processing', $item['id'], false, $e->getMessage());
            }
        }
        
        $isCompleted = $processedCount === $totalItems;
        $hasErrors = $processedCount < $totalItems;
        $progress = $totalItems > 0 ? ($processedCount / $totalItems) * 100 : 100;
        
        // Basic response for standard webhooks
        if (!$request->has('detailed_response')) {
            return new TriggerResponse(
                is_completed: $isCompleted,
                is_error: $hasErrors,
                progress_in_percentage: (int) $progress,
                interval_in_seconds: $hasErrors ? 30 : null,
                data: [
                    'job_id' => $jobId,
                    'processed_count' => $processedCount,
                    'total_items' => $totalItems
                ]
            );
        }
        
        // Enhanced response with processing summary
        return new TriggerResponse(
            is_completed: $isCompleted,
            is_error: $hasErrors,
            progress_in_percentage: (int) $progress,
            interval_in_seconds: $hasErrors ? 30 : null,
            data: [
                'job_id' => $jobId,
                'processed_count' => $processedCount,
                'total_items' => $totalItems,
                'success_rate' => $totalItems > 0 ? ($processedCount / $totalItems) * 100 : 0
            ],
            include_processing_in_data: true // Include processing summary
        );
    }
}
```

### Export Controller with Processing Tracking

```php
class DataExportController extends Controller
{
    public function export(Request $request)
    {
        $filters = $request->input('filters', []);
        $format = $request->input('format', 'json');
        
        // Get data to export
        $dataToExport = $this->getExportData($filters);
        $exportedData = [];
        
        foreach ($dataToExport as $item) {
            try {
                // Transform item for export
                $transformedItem = $this->transformForExport($item, $format);
                $exportedData[] = $transformedItem;
                
                // Track export processing
                $request->trackSaasId($item->saas_id, true);
                $request->trackCustom('data_export', $item->id, true, null, [
                    'format' => $format,
                    'item_type' => $item->type,
                    'size_bytes' => strlen(json_encode($transformedItem))
                ]);
                
            } catch (Exception $e) {
                $request->trackSaasId($item->saas_id, false, $e->getMessage());
                $request->trackCustom('data_export', $item->id, false, $e->getMessage());
            }
        }
        
        $success = count($exportedData) === count($dataToExport);
        
        return new ExportResponse(
            success: $success,
            message: $success 
                ? "Successfully exported " . count($exportedData) . " items"
                : "Export completed with some failures",
            data: [
                'exported_data' => $exportedData,
                'export_metadata' => [
                    'format' => $format,
                    'total_items' => count($dataToExport),
                    'exported_items' => count($exportedData),
                    'filters_applied' => $filters,
                    'exported_at' => now()->toISOString()
                ]
            ],
            include_processing_summary: $request->boolean('include_stats', false)
        );
    }
}
```

## Migration from Existing Code

### Zero Changes Required
```php
// This existing code works exactly the same
return new TriggerResponse(
    is_completed: true,
    progress_in_percentage: 100
);

return new ImportResponse(
    success: true,
    message: "Import completed",
    data: $results
);

return new ExportResponse(
    success: true,
    message: "Export completed", 
    data: $exportedData
);
```

### Optional Enhancements
```php
// Add optional processing features when needed
return new TriggerResponse(
    is_completed: true,
    progress_in_percentage: 100,
    data: $data,
    include_processing_in_data: true // NEW: Optional parameter
);

return new ImportResponse(
    success: true,
    message: "Import completed",
    data: $results,
    include_processing_summary: true, // NEW: Optional parameter
    include_processing_details: true  // NEW: Optional parameter
);
```

## Benefits of Merged Approach

✅ **Backward Compatible** - All existing code works unchanged  
✅ **Optional Enhancement** - New features via optional constructor parameters  
✅ **Same Structure** - No breaking changes to response format  
✅ **Flexible Control** - Choose when to include processing data  
✅ **Progressive Adoption** - Add features where needed, when needed  
✅ **Zero Refactoring** - Existing controllers require no changes  

Your response classes now have enhanced processing capabilities while maintaining complete backward compatibility!