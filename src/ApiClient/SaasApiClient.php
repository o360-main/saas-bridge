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
        return new ModuleApi($this->api, EndPoint::currency);
    }

    //now follow same as upper with all the EndPoints


    //stores
    public function stores(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::store);

    }

    //tax
    public function taxes(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::tax);
    }

    //attribute
    public function attributes(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::attribute);
    }


    //category
    public function categories(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::category);
    }

    //payment-method
    public function payment_methods(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::payment_method);
    }


    //tier-group
    public function tier_groups(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::tier_group);

    }

    //product
    public function products(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::product);
    }

    //inventory
    public function inventories(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::inventory);
    }

    //customer
    public function customers(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::customer);
    }

    //seller
    public function sellers(): ModuleApi
    {

        return new ModuleApi($this->api, EndPoint::seller);

    }

    //order
    public function orders(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::order);
    }
}
