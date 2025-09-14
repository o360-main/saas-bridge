<?php

namespace O360Main\SaasBridge\Traits;

use O360Main\SaasBridge\Helpers\RequestResponseContext;
use Illuminate\Http\JsonResponse;

trait AttachesProcessingStats
{
    /**
     * Attach processing stats to the response
     */
    protected function attachProcessingStats(JsonResponse $response): JsonResponse
    {
        $config = config('saas-bridge.request_context', []);
        
        if (!($config['enabled'] ?? true) || !($config['auto_attach'] ?? true)) {
            return $response;
        }
        
        $attachMode = $config['attach_mode'] ?? 'header';
        $context = RequestResponseContext::getInstance();
        $stats = $context->getProcessingStats();
        
        // Attach as header
        if (in_array($attachMode, ['header', 'both'])) {
            $response->headers->set('X-Processing-Stats', json_encode([
                'connection_id' => $context->getConnectionId(),
                'record_id' => $context->getRecordId(),
                'record_log_id' => $context->getRecordLogId(),
                'processed_count' => $context->getProcessedCount(),
                'duration_ms' => $stats['processing']['duration_ms'],
                'success_count' => $stats['processing']['success_count'],
                'error_count' => $stats['processing']['error_count'],
            ]));
        }
        
        // Attach to response body
        if (in_array($attachMode, ['body', 'both'])) {
            $originalData = $response->getData(true);
            
            if (is_array($originalData)) {
                $originalData['_processing'] = $stats;
                $response->setData($originalData);
            }
        }
        
        return $response;
    }
    
    /**
     * Get processing summary for including in response data
     */
    protected function getProcessingSummary(): array
    {
        $context = RequestResponseContext::getInstance();
        $stats = $context->getProcessingStats();
        
        return [
            'connection_id' => $context->getConnectionId(),
            'record_id' => $context->getRecordId(),
            'record_log_id' => $context->getRecordLogId(),
            'processed_count' => $context->getProcessedCount(),
            'success_count' => $stats['processing']['success_count'],
            'error_count' => $stats['processing']['error_count'],
            'duration_ms' => $stats['processing']['duration_ms'],
            'has_errors' => $context->hasErrors(),
        ];
    }
    
    /**
     * Get full processing details
     */
    protected function getProcessingDetails(): array
    {
        return RequestResponseContext::getInstance()->getProcessingStats();
    }
}