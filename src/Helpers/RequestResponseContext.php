<?php

namespace O360Main\SaasBridge\Helpers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

class RequestResponseContext
{
    private static ?self $instance = null;
    
    private ?string $connectionId = null;
    private ?string $recordId = null;
    private ?string $recordLogId = null;
    private array $trackingData = [];
    private float $startTime;
    private ?string $controllerAction = null;
    private array $errors = [];
    private int $processedCount = 0;
    
    private function __construct()
    {
        $this->startTime = microtime(true);
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize context from request headers (called from middleware)
     */
    public function initFromRequest(Request $request): self
    {
        $config = config('saas-bridge.request_context', []);
        $headers = $config['headers'] ?? [
            'connection_id' => ['X-Connection-ID', 'Connection-ID', 'connection-id'],
            'record_id' => ['X-Record-ID', 'Record-ID', 'record-id'],
            'record_log_id' => ['X-Record-Log-ID', 'Record-Log-ID', 'record-log-id']
        ];
        
        // Extract headers
        foreach ($headers['connection_id'] as $header) {
            if ($connectionId = $request->header($header)) {
                $this->connectionId = $connectionId;
                break;
            }
        }
        
        foreach ($headers['record_id'] as $header) {
            if ($recordId = $request->header($header)) {
                $this->recordId = $recordId;
                break;
            }
        }
        
        foreach ($headers['record_log_id'] as $header) {
            if ($recordLogId = $request->header($header)) {
                $this->recordLogId = $recordLogId;
                break;
            }
        }
        
        // Set controller action
        $route = $request->route();
        if ($route) {
            $action = $route->getActionName();
            $this->controllerAction = $action;
        }
        
        // Store in Redis for cross-request access
        $this->storeContextInRedis();
        
        return $this;
    }
    
    /**
     * Track SaaS ID processing
     */
    public function trackSaasId(string $saasId, bool $success = true, ?string $error = null): self
    {
        $this->trackingData['saas_ids'][] = [
            'id' => $saasId,
            'success' => $success,
            'error' => $error,
            'timestamp' => now()->toISOString(),
            'processed_at' => microtime(true)
        ];
        
        if ($success) {
            $this->processedCount++;
        }
        
        if ($error) {
            $this->errors[] = [
                'type' => 'saas_id',
                'id' => $saasId,
                'error' => $error
            ];
        }
        
        $this->updateRedisContext();
        return $this;
    }
    
    /**
     * Track Sync ID processing
     */
    public function trackSyncId(string $syncId, bool $success = true, ?string $error = null): self
    {
        $this->trackingData['sync_ids'][] = [
            'id' => $syncId,
            'success' => $success,
            'error' => $error,
            'timestamp' => now()->toISOString(),
            'processed_at' => microtime(true)
        ];
        
        if ($success) {
            $this->processedCount++;
        }
        
        if ($error) {
            $this->errors[] = [
                'type' => 'sync_id',
                'id' => $syncId,
                'error' => $error
            ];
        }
        
        $this->updateRedisContext();
        return $this;
    }
    
    /**
     * Track Platform ID processing
     */
    public function trackPlatformId(string $platformId, bool $success = true, ?string $error = null): self
    {
        $this->trackingData['platform_ids'][] = [
            'id' => $platformId,
            'success' => $success,
            'error' => $error,
            'timestamp' => now()->toISOString(),
            'processed_at' => microtime(true)
        ];
        
        if ($success) {
            $this->processedCount++;
        }
        
        if ($error) {
            $this->errors[] = [
                'type' => 'platform_id',
                'id' => $platformId,
                'error' => $error
            ];
        }
        
        $this->updateRedisContext();
        return $this;
    }
    
    /**
     * Track custom data processing
     */
    public function trackCustom(string $type, string $id, bool $success = true, ?string $error = null, array $metadata = []): self
    {
        $this->trackingData['custom'][$type][] = [
            'id' => $id,
            'success' => $success,
            'error' => $error,
            'metadata' => $metadata,
            'timestamp' => now()->toISOString(),
            'processed_at' => microtime(true)
        ];
        
        if ($success) {
            $this->processedCount++;
        }
        
        if ($error) {
            $this->errors[] = [
                'type' => $type,
                'id' => $id,
                'error' => $error
            ];
        }
        
        $this->updateRedisContext();
        return $this;
    }
    
    /**
     * Increment processed count
     */
    public function incrementProcessed(int $count = 1): self
    {
        $this->processedCount += $count;
        $this->updateRedisContext();
        return $this;
    }
    
    /**
     * Add general error
     */
    public function addError(string $type, string $message, ?string $id = null): self
    {
        $this->errors[] = [
            'type' => $type,
            'id' => $id,
            'error' => $message,
            'timestamp' => now()->toISOString()
        ];
        
        $this->updateRedisContext();
        return $this;
    }
    
    /**
     * Get processing stats
     */
    public function getProcessingStats(): array
    {
        $duration = microtime(true) - $this->startTime;
        
        return [
            'request_context' => [
                'connection_id' => $this->connectionId,
                'record_id' => $this->recordId,
                'record_log_id' => $this->recordLogId,
                'controller_action' => $this->controllerAction,
            ],
            'processing' => [
                'total_processed' => $this->processedCount,
                'duration_ms' => round($duration * 1000, 2),
                'start_time' => date('c', $this->startTime),
                'end_time' => now()->toISOString(),
                'success_count' => $this->getSuccessCount(),
                'error_count' => count($this->errors),
            ],
            'tracking_data' => $this->trackingData,
            'errors' => $this->errors,
        ];
    }
    
    /**
     * Get success count from tracking data
     */
    private function getSuccessCount(): int
    {
        $successCount = 0;
        
        foreach ($this->trackingData as $type => $items) {
            if (is_array($items)) {
                if ($type === 'custom') {
                    foreach ($items as $customType => $customItems) {
                        $successCount += collect($customItems)->where('success', true)->count();
                    }
                } else {
                    $successCount += collect($items)->where('success', true)->count();
                }
            }
        }
        
        return $successCount;
    }
    
    /**
     * Auto-attach processing stats to JSON response
     */
    public function attachToResponse(JsonResponse $response): JsonResponse
    {
        $config = config('saas-bridge.request_context', []);
        $attachMode = $config['attach_mode'] ?? 'header'; // 'header', 'body', or 'both'
        $stats = $this->getProcessingStats();
        
        if (in_array($attachMode, ['header', 'both'])) {
            $response->headers->set('X-Processing-Stats', json_encode([
                'connection_id' => $this->connectionId,
                'record_id' => $this->recordId,
                'processed_count' => $this->processedCount,
                'duration_ms' => $stats['processing']['duration_ms'],
                'success_count' => $stats['processing']['success_count'],
                'error_count' => $stats['processing']['error_count'],
            ]));
        }
        
        if (in_array($attachMode, ['body', 'both'])) {
            $originalData = $response->getData(true);
            
            if (is_array($originalData)) {
                $originalData['_processing'] = $stats;
            } else {
                $originalData = [
                    'data' => $originalData,
                    '_processing' => $stats
                ];
            }
            
            $response->setData($originalData);
        }
        
        return $response;
    }
    
    /**
     * Store context data in Redis
     */
    private function storeContextInRedis(): void
    {
        if (!$this->connectionId) {
            return;
        }
        
        $key = $this->getRedisKey();
        $ttl = config('saas-bridge.request_context.redis_ttl', 3600); // 1 hour default
        
        $contextData = [
            'connection_id' => $this->connectionId,
            'record_id' => $this->recordId,
            'record_log_id' => $this->recordLogId,
            'controller_action' => $this->controllerAction,
            'start_time' => $this->startTime,
            'tracking_data' => $this->trackingData,
            'processed_count' => $this->processedCount,
            'errors' => $this->errors,
            'updated_at' => now()->toISOString(),
        ];
        
        Redis::setex($key, $ttl, json_encode($contextData));
    }
    
    /**
     * Update Redis context
     */
    private function updateRedisContext(): void
    {
        $this->storeContextInRedis();
    }
    
    /**
     * Get Redis key for context storage
     */
    private function getRedisKey(): string
    {
        $prefix = config('saas-bridge.request_context.redis_key_prefix', 'request_context');
        return "{$prefix}:{$this->connectionId}";
    }
    
    /**
     * Load context from Redis (useful for async jobs or background tasks)
     */
    public static function loadFromRedis(string $connectionId): ?self
    {
        $prefix = config('saas-bridge.request_context.redis_key_prefix', 'request_context');
        $key = "{$prefix}:{$connectionId}";
        
        $data = Redis::get($key);
        if (!$data) {
            return null;
        }
        
        $contextData = json_decode($data, true);
        if (!$contextData) {
            return null;
        }
        
        $instance = new self();
        $instance->connectionId = $contextData['connection_id'];
        $instance->recordId = $contextData['record_id'];
        $instance->recordLogId = $contextData['record_log_id'];
        $instance->controllerAction = $contextData['controller_action'];
        $instance->startTime = $contextData['start_time'];
        $instance->trackingData = $contextData['tracking_data'] ?? [];
        $instance->processedCount = $contextData['processed_count'] ?? 0;
        $instance->errors = $contextData['errors'] ?? [];
        
        return $instance;
    }
    
    /**
     * Get connection ID
     */
    public function getConnectionId(): ?string
    {
        return $this->connectionId;
    }
    
    /**
     * Get record ID
     */
    public function getRecordId(): ?string
    {
        return $this->recordId;
    }
    
    /**
     * Get record log ID
     */
    public function getRecordLogId(): ?string
    {
        return $this->recordLogId;
    }
    
    /**
     * Get controller action
     */
    public function getControllerAction(): ?string
    {
        return $this->controllerAction;
    }
    
    /**
     * Get processed count
     */
    public function getProcessedCount(): int
    {
        return $this->processedCount;
    }
    
    /**
     * Check if context has errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
    
    /**
     * Get all errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Clear context (useful for testing or cleanup)
     */
    public function clear(): self
    {
        $this->trackingData = [];
        $this->errors = [];
        $this->processedCount = 0;
        return $this;
    }
    
    /**
     * Reset singleton instance
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
    
    /**
     * Create helper function for easy access
     */
    public static function track(): self
    {
        return self::getInstance();
    }
    
    /**
     * Static helper methods for direct access without getInstance()
     */
    public static function saasId(string $saasId, bool $success = true, ?string $error = null): void
    {
        self::getInstance()->trackSaasId($saasId, $success, $error);
    }
    
    public static function syncId(string $syncId, bool $success = true, ?string $error = null): void
    {
        self::getInstance()->trackSyncId($syncId, $success, $error);
    }
    
    public static function platformId(string $platformId, bool $success = true, ?string $error = null): void
    {
        self::getInstance()->trackPlatformId($platformId, $success, $error);
    }
    
    public static function custom(string $type, string $id, bool $success = true, ?string $error = null, array $metadata = []): void
    {
        self::getInstance()->trackCustom($type, $id, $success, $error, $metadata);
    }
    
    public static function processed(int $count = 1): void
    {
        self::getInstance()->incrementProcessed($count);
    }
    
    public static function error(string $type, string $message, ?string $id = null): void
    {
        self::getInstance()->addError($type, $message, $id);
    }
    
    public static function apiResponse(array $response, string $type = 'api_call'): void
    {
        $context = self::getInstance();
        
        // Auto-detect sync_id from response
        if (isset($response['sync_id'])) {
            $context->trackSyncId($response['sync_id'], true);
        }
        
        // Track the API call itself
        $context->trackCustom($type, uniqid(), true, null, [
            'response_keys' => array_keys($response),
            'has_sync_id' => isset($response['sync_id'])
        ]);
    }
}