<?php

namespace O360Main\SaasBridge\Traits;

use O360Main\SaasBridge\Helpers\RequestResponseContext;

trait TracksProcessing
{
    /**
     * Get request context instance
     */
    protected function context(): RequestResponseContext
    {
        return RequestResponseContext::getInstance();
    }
    
    /**
     * Track SaaS ID processing
     */
    protected function trackSaasId(string $saasId, bool $success = true, ?string $error = null): self
    {
        $this->context()->trackSaasId($saasId, $success, $error);
        return $this;
    }
    
    /**
     * Track Sync ID processing
     */
    protected function trackSyncId(string $syncId, bool $success = true, ?string $error = null): self
    {
        $this->context()->trackSyncId($syncId, $success, $error);
        return $this;
    }
    
    /**
     * Track Platform ID processing
     */
    protected function trackPlatformId(string $platformId, bool $success = true, ?string $error = null): self
    {
        $this->context()->trackPlatformId($platformId, $success, $error);
        return $this;
    }
    
    /**
     * Track custom data processing
     */
    protected function trackCustom(string $type, string $id, bool $success = true, ?string $error = null, array $metadata = []): self
    {
        $this->context()->trackCustom($type, $id, $success, $error, $metadata);
        return $this;
    }
    
    /**
     * Increment processed count
     */
    protected function incrementProcessed(int $count = 1): self
    {
        $this->context()->incrementProcessed($count);
        return $this;
    }
    
    /**
     * Add error to tracking
     */
    protected function addTrackingError(string $type, string $message, ?string $id = null): self
    {
        $this->context()->addError($type, $message, $id);
        return $this;
    }
    
    /**
     * Get processing stats
     */
    protected function getProcessingStats(): array
    {
        return $this->context()->getProcessingStats();
    }
    
    /**
     * Get connection ID from context
     */
    protected function getConnectionId(): ?string
    {
        return $this->context()->getConnectionId();
    }
    
    /**
     * Get record ID from context
     */
    protected function getRecordId(): ?string
    {
        return $this->context()->getRecordId();
    }
    
    /**
     * Get plugin record ID from context
     */
    protected function getPluginRecordId(): ?string
    {
        return $this->context()->getPluginRecordId();
    }
    
    /**
     * Check if processing has errors
     */
    protected function hasProcessingErrors(): bool
    {
        return $this->context()->hasErrors();
    }
    
    /**
     * Get processing errors
     */
    protected function getProcessingErrors(): array
    {
        return $this->context()->getErrors();
    }
    
    /**
     * Track multiple items at once
     */
    protected function trackBatch(string $type, array $items): self
    {
        foreach ($items as $item) {
            if (is_array($item)) {
                $this->context()->trackCustom(
                    $type,
                    $item['id'],
                    $item['success'] ?? true,
                    $item['error'] ?? null,
                    $item['metadata'] ?? []
                );
            } else {
                $this->context()->trackCustom($type, $item, true);
            }
        }
        return $this;
    }
    
    /**
     * Track API response processing (auto-detect sync_id)
     */
    protected function trackApiResponse(array $response, string $type = 'api_call'): self
    {
        // Auto-detect sync_id from response
        if (isset($response['sync_id'])) {
            $this->trackSyncId($response['sync_id'], true);
        }
        
        // Track the API call itself
        $this->trackCustom($type, uniqid(), true, null, [
            'response_keys' => array_keys($response),
            'has_sync_id' => isset($response['sync_id'])
        ]);
        
        return $this;
    }
    
    /**
     * Track model operations
     */
    protected function trackModelOperation(string $operation, $model, bool $success = true, ?string $error = null): self
    {
        $modelClass = is_object($model) ? get_class($model) : $model;
        $id = is_object($model) && method_exists($model, 'getKey') ? $model->getKey() : 'unknown';
        
        $this->trackCustom("model_{$operation}", $id, $success, $error, [
            'model_class' => $modelClass,
            'operation' => $operation
        ]);
        
        return $this;
    }
    
    /**
     * Helper method to track success with auto-increment
     */
    protected function trackSuccess(string $type, string $id, array $metadata = []): self
    {
        $this->trackCustom($type, $id, true, null, $metadata);
        return $this;
    }
    
    /**
     * Helper method to track failure without auto-increment
     */
    protected function trackFailure(string $type, string $id, string $error, array $metadata = []): self
    {
        $this->trackCustom($type, $id, false, $error, $metadata);
        return $this;
    }
}