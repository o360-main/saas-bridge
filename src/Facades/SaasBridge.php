<?php

namespace O360Main\SaasBridge\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Http\Client\PendingRequest api($version = null)
 * @method static array credentials()
 * @method static \O360Main\SaasBridge\SaasBridgeService getInstance()
 * @method static array config()
 * @method static array configurations()
 * @method static array moduleConfig()
 * @see \O360Main\SaasBridge\SaasBridgeService
 */
class SaasBridge extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'saas-bridge';
    }
}
