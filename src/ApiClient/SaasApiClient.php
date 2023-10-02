<?php

namespace O360Main\SaasBridge\ApiClient;

use O360Main\SaasBridge\SaasAgent;

class SaasApiClient
{
    private \Illuminate\Http\Client\PendingRequest $api;

    /**
     * @throws \Exception
     */
    public function __construct(
        private readonly SaasAgent $saasAgent, private readonly ?string $version = null)
    {
        $this->api = $this->saasAgent->saasApi($this->version);
    }


    public function api(): \Illuminate\Http\Client\PendingRequest
    {
        return $this->api;
    }


    /**
     * Currency SaaS Api
     *
     * @return \O360Main\SaasBridge\ApiClient\ModuleApi
     */
    public function currencies(): ModuleApi
    {
        $api = $this->api->baseUrl(EndPoint::currency->value);

        return new ModuleApi($api);
    }

    //now follow same as upper with all the EndPoints


    //stores
    public function stores(): ModuleApi
    {
        $api = $this->api->baseUrl(EndPoint::store->value);

        return new ModuleApi($api);
    }

    //tax
    public function taxes(): ModuleApi
    {
        $api = $this->api->baseUrl(EndPoint::tax->value);

        return new ModuleApi($api);
    }

    //attribute
    public function attributes(): ModuleApi
    {
        $api = $this->api->baseUrl(EndPoint::attribute->value);

        return new ModuleApi($api);
    }


    //category
    public function categories(): ModuleApi
    {
        $api = $this->api->baseUrl(EndPoint::category->value);

        return new ModuleApi($api);
    }

    //payment-method
    public function payment_methods(): ModuleApi
    {
        $api = $this->api->baseUrl(EndPoint::payment_method->value);

        return new ModuleApi($api);
    }


    //tier-group
    public function tier_groups(): ModuleApi
    {
        $api = $this->api->baseUrl(EndPoint::tier_group->value);

        return new ModuleApi($api);
    }

    //product
    public function products(): ModuleApi
    {
        $api = $this->api->baseUrl(EndPoint::product->value);

        return new ModuleApi($api);
    }

    //inventory
    public function inventories(): ModuleApi
    {
        $api = $this->api->baseUrl(EndPoint::inventory->value);

        return new ModuleApi($api);
    }

    //customer
    public function customers(): ModuleApi
    {
        $api = $this->api->baseUrl(EndPoint::customer->value);

        return new ModuleApi($api);
    }

    //seller
    public function sellers(): ModuleApi
    {
        $api = $this->api->baseUrl(EndPoint::seller->value);

        return new ModuleApi($api);
    }

    //order
    public function orders(): ModuleApi
    {
        $api = $this->api->baseUrl(EndPoint::order->value);

        return new ModuleApi($api);
    }
}
