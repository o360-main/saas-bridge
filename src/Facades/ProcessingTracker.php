<?php

namespace O360Main\SaasBridge\Facades;

use Illuminate\Support\Facades\Facade;
use O360Main\SaasBridge\Helpers\RequestResponseContext;

/**
 * ProcessingTracker Facade
 * 
 * @method static void saasId(string $saasId, bool $success = true, ?string $error = null)
 * @method static void syncId(string $syncId, bool $success = true, ?string $error = null)
 * @method static void platformId(string $platformId, bool $success = true, ?string $error = null)
 * @method static void custom(string $type, string $id, bool $success = true, ?string $error = null, array $metadata = [])
 * @method static void processed(int $count = 1)
 * @method static void error(string $type, string $message, ?string $id = null)
 * @method static void apiResponse(array $response, string $type = 'api_call')
 * @method static \O360Main\SaasBridge\Helpers\RequestResponseContext track()
 * @method static array getProcessingStats()
 * @method static string|null getConnectionId()
 * @method static string|null getRecordId()
 * @method static string|null getPluginRecordId()
 * @method static bool hasErrors()
 * @method static array getErrors()
 * @method static int getProcessedCount()
 */
class ProcessingTracker extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return RequestResponseContext::class;
    }
}