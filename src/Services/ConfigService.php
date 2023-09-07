<?php

namespace O360Main\SaasBridge\Services;

use Illuminate\Support\Arr;


/**
 * @method array|mixed currencies($key = null)
 * @method array|mixed taxes($key = null)
 * @method array|mixed attributes($key = null)
 * @method array|mixed categories($key = null)
 * @method array|mixed paymentMethods($key = null)
 * @method array|mixed products($key = null)
 * @method array|mixed stocks($key = null)
 * @method array|mixed customers($key = null)
 * @method array|mixed orders($key = null)
 * @method array|mixed sellPricing($key = null)
 * @method array|mixed stores($key = null)
 *
 */
class ConfigService
{
    //stores
    //currencies
    //taxes
    //attributes
    //categories
    //payment-methods
    //products
    //stocks
    //customers
    //orders
    //sell_pricing

    private $arr = [];

    public function __construct(array $arr)
    {
        $this->arr = $arr;
    }

    public function get($name)
    {
        return Arr::get($this->arr, $name, null);
    }


    public function __call($name, $arguments)
    {
        if (!empty($arguments)) {
            return Arr::get($this->arr, $name . '.' . $arguments[0], null);
        }

        if (isset($this->arr[$name])) {
            return $this->arr[$name];
        }

        return [];
    }

}
