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
    private array $_plugin;
    private array $_dataConfig;
    private array $_source;
    private array $_enabled;

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
    public function saasApi($version = 'v1'): PendingRequest
    {
        $baseUrl = SaasConfig::getInstance()->coreUrl();

        if ($version !== null) {
            $allowedVersions = [
                'v1'
            ];
            if (!in_array($version, $allowedVersions)) {
                throw new \Exception('Invalid version, Allowed versions ' . implode(', ', $allowedVersions));
            }

            $arr = [$baseUrl, $version];

            $baseUrl = implode('/', array_map(fn ($i) => rtrim($i, '/'), $arr));

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

    public function setPlugin(array $plugin): void
    {
        $this->_plugin = $plugin;
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


    public function setDataConfig(array $config): void
    {
        $this->_dataConfig = $config;
    }

    public function dataConfig($key = null): array
    {
        if ($key !== null) {
            return $this->_dataConfig[$key] ?? [];
        }

        return $this->_dataConfig;
    }


    public function setSource(array $source): void
    {
        $this->_source = $source;
    }

    public function source($module = null)
    {
        if ($module !== null) {
            return $this->_source[$module] ?? null;
        }

        return $this->_source;
    }

    public function mainModules($module = null)
    {
        if ($module !== null) {
            return $this->_source[$module] ?? null;
        }

        return $this->_source;
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

    public function plugin(): array
    {
        return $this->_plugin;
    }

    public function setEnabled(array $param): void
    {
        $this->_enabled = $param;
    }

    public function enabledModules($module = null)
    {
        if ($module !== null) {
            return $this->_enabled[$module] ?? null;
        }

        return $this->_enabled;
    }

}
