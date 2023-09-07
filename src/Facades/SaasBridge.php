<?php

namespace O360Main\SaasBridge\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Http\Client\PendingRequest api($version = null)
 * @method static \O360Main\SaasBridge\ApiClient\SaasApiClient apiClient($version = null)
 * @method static array credentials()
 * @method static array config()
 * @method static \O360Main\SaasBridge\Services\ConfigService configService()
 * @method static array configurations()
 * @method static array moduleConfig()
 * @method static \O360Main\SaasBridge\SaasBridgeService getInstance()
 * @see \O360Main\SaasBridge\SaasBridgeService
 */
class SaasBridge extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'saas-bridge';
    }
}
