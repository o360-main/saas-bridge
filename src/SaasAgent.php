<?php

namespace O360Main\SaasBridge;

use Illuminate\Http\Client\PendingRequest;

class SaasAgent
{
    //make singleton
    private static ?SaasAgent $instance = null;
    private array $_config;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @var PendingRequest
     */
    private PendingRequest $_saasApi;

    public function setSaasApi(PendingRequest $saasApi): void
    {
        $this->_saasApi = $saasApi;
    }

    //getter setter
    public function saasApi(): PendingRequest
    {
        return $this->_saasApi;
    }


    public function setConfig(array $config): void
    {
        $this->_config = $config;
    }

    public function config(): array
    {
        return $this->_config;
    }

    //call with magic method also return above function
//    public function __call($name, $arguments)
//    {
//        return $this->saasApi()->$name(...$arguments);
//    }

}

