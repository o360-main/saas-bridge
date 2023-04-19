<?php

namespace O360Main\SaasBridge\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static  \Illuminate\Http\Client\PendingRequest api()
 * @method static array credentials()
 * @method static array configs()
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
