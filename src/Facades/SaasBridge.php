<?php

namespace O360Main\SaasBridge\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Http\Client\PendingRequest api($version = null)
 * @method static \O360Main\SaasBridge\ApiClient\SaasApiClient apiClient($version = null)
 * @method static array credentials()
 * @method static array config()
 * @method static array connection()
 * @method static array plugin()
 * @method static string pluginId()
 * @method static \O360Main\SaasBridge\Services\ConfigService configService()
 * @method static array configurations($module = null)
 * @method static array moduleConfig($module = null)
 * @method static array dataConfig($module = null)
 * @method static array enabledModules($module = null)
 * @method static array mainModules($module = null)
 * @method static array source($module = null)

 * @method static array metaDataConfig($module = null)
 * @method static \O360Main\SaasBridge\SaasBridgeService getInstance()
 * @see \O360Main\SaasBridge\SaasBridgeService
 *

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
