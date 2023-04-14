<?php

namespace O360Main\SaasBridge;

use Illuminate\Support\Facades\Facade;

/**
 * @method  \Illuminate\Http\Client\PendingRequest api()
 * @method  array credentials()
 * @method  array configs()
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
