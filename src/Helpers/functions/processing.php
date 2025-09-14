<?php

use O360Main\SaasBridge\Helpers\RequestResponseContext;

if (!function_exists('track_saas_id')) {
    /**
     * Track SaaS ID processing
     */
    function track_saas_id(string $saasId, bool $success = true, ?string $error = null): void
    {
        RequestResponseContext::saasId($saasId, $success, $error);
    }
}

if (!function_exists('track_sync_id')) {
    /**
     * Track Sync ID processing
     */
    function track_sync_id(string $syncId, bool $success = true, ?string $error = null): void
    {
        RequestResponseContext::syncId($syncId, $success, $error);
    }
}

if (!function_exists('track_platform_id')) {
    /**
     * Track Platform ID processing
     */
    function track_platform_id(string $platformId, bool $success = true, ?string $error = null): void
    {
        RequestResponseContext::platformId($platformId, $success, $error);
    }
}

if (!function_exists('track_custom')) {
    /**
     * Track custom data processing
     */
    function track_custom(string $type, string $id, bool $success = true, ?string $error = null, array $metadata = []): void
    {
        RequestResponseContext::custom($type, $id, $success, $error, $metadata);
    }
}

if (!function_exists('track_api_response')) {
    /**
     * Track API response (auto-detects sync_id)
     */
    function track_api_response(array $response, string $type = 'api_call'): void
    {
        RequestResponseContext::apiResponse($response, $type);
    }
}

if (!function_exists('increment_processed')) {
    /**
     * Increment processed count
     */
    function increment_processed(int $count = 1): void
    {
        RequestResponseContext::processed($count);
    }
}

if (!function_exists('add_tracking_error')) {
    /**
     * Add tracking error
     */
    function add_tracking_error(string $type, string $message, ?string $id = null): void
    {
        RequestResponseContext::error($type, $message, $id);
    }
}

if (!function_exists('get_processing_stats')) {
    /**
     * Get current processing statistics
     */
    function get_processing_stats(): array
    {
        return RequestResponseContext::getInstance()->getProcessingStats();
    }
}

if (!function_exists('processing_context')) {
    /**
     * Get processing context instance
     */
    function processing_context(): RequestResponseContext
    {
        return RequestResponseContext::getInstance();
    }
}