<?php

namespace O360Main\SaasBridge;

use Illuminate\Http\Client\PendingRequest;

class SaasAgent
{
    //make singleton
    private static self|null $instance = null;
    private array $_credentials;

    private array $_moduleConfig;
    private array $_connection;

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

    /**
     * @throws \Exception
     */
    public function saasApi($version = null): PendingRequest
    {
        if ($version !== null) {
            $allowedVersions = [
                'v1'
            ];
            if (!in_array($version, $allowedVersions)) {
                throw new \Exception('Invalid version, Allowed versions ' . implode(', ', $allowedVersions));
            }

            $baseUrl = config('saas-bridge.saas_api_url') . '/' . $version;

            $this->_saasApi->baseUrl($baseUrl);
        }

        return $this->_saasApi;
    }


    public function setCredentials(array $credentials): void
    {
        $this->_credentials = $credentials;
    }

    public function setConnection(array $connection): void
    {
        $this->_connection = $connection;
    }

    public function credentials(): array
    {
        return $this->_credentials;
    }


    public function setModuleConfig(array $config): void
    {
        $this->_moduleConfig = $config;
    }

    public function moduleConfig($key = null): array
    {
        if ($key !== null) {
            return $this->_moduleConfig[$key] ?? [];
        }

        return $this->_moduleConfig;
    }



    //call with magic method also return above function
//    public function __call($name, $arguments)
//    {
//        return $this->saasApi()->$name(...$arguments);
//    }
    public function connection(): array
    {
        return $this->_connection;
    }

}
