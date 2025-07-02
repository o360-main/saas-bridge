<?php

namespace O360Main\SaasBridge\ApiClient;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use O360Main\SaasBridge\SaasAgent;

class SaasApiClient
{
    private \Illuminate\Http\Client\PendingRequest $api;

    /**
     * @throws \Exception
     */
    public function __construct(
        private readonly SaasAgent $saasAgent,
        private readonly ?string $version = null
    ) {
        $this->api = $this->saasAgent->saasApi($this->version);
    }


    public function api(): \Illuminate\Http\Client\PendingRequest
    {
        return $this->api;
    }


    public function forModule(EndPoint $endPoint): ModuleApi
    {
        return new ModuleApi($this->api, $endPoint);
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

    //discount
    public function discounts(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::discount);
    }

    //gift-card
    public function gift_cards(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::gift_card);
    }

    public function shipping_methods(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::shipping_method);
    }

    //tier-price
    public function tier_prices(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::tier_price);
    }

    public function product_price_classes(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::product_price_class);
    }

    public function order_items(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::order_item);
    }

    public function product_images(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::product_image);
    }

    public function order_reason_codes(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::order_reason_code);
    }

    public function addresses(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::addresses);
    }

    //data-countries
    public function data_countries(): PromiseInterface|Response
    {
        return $this->api->get('/data/countries');
    }

    //data-currencies
    public function data_currencies(): PromiseInterface|Response
    {
        return $this->api->get('/data/currencies');
    }

    public function error_logs(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::error_log);
    }

    public function order_returns(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::order_return);
    }

    public function order_refunds(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::order_refund);
    }

    public function order_return_items(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::order_return_item);
    }

    public function order_refund_items(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::order_refund_item);
    }

    public function order_shippings(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::order_shipping);
    }

    public function order_payment_methods(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::order_payment_method);
    }
    //plugin-logs
    public function plugin_logs(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::plugin_log);
    }

    public function cache_datas(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::cache_data);
    }

    //catalogs
    public function catalogs(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::catalog);
    }

    //companies
    public function companies(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::company);
    }

    public function loyalty_points(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::loyalty_points);
    }

    public function accounts(): ModuleApi
    {
        return new ModuleApi($this->api, EndPoint::account);
    }
}
