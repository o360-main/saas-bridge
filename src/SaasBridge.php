<?php

namespace O360Main\SaasBridge;

use Illuminate\Support\Facades\Facade;

/**
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
