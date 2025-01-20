<?php

namespace O360Main\SaasBridge;

use Illuminate\Http\Client\PendingRequest;
use O360Main\SaasBridge\ApiClient\SaasApiClient;
use O360Main\SaasBridge\Services\ConfigService;

class SaasBridgeService
{
    private readonly ConfigService $configService;

    public function __construct(private readonly SaasAgent $saasAgent)
    {
        $this->configService = new ConfigService($this->saasAgent->moduleConfig());
    }

    public function getInstance(): self
    {
        return $this;
    }

    // Build your next great package.

    /**
     * @throws \Exception
     */
    public function api($version = null): PendingRequest
    {
        return $this->saasAgent->saasApi($version);
    }

    /**
     * @throws \Exception
     */
    public function apiClient($version = null): SaasApiClient
    {
        return new SaasApiClient($this->saasAgent, $version);
    }

    public function credentials(): array
    {
        return $this->saasAgent->credentials();
    }

    public function configurations($key = null): array
    {
        return $this->saasAgent->moduleConfig($key);
    }

    public function config($module = null): array
    {
        return $this->saasAgent->moduleConfig($module);
    }

    /**
     * @deprecated  use mainModules
     *
     * @return array|mixed|null
     */
    public function source($module = null)
    {
        return $this->saasAgent->source($module);
    }

    public function mainModules($module = null)
    {
        return $this->saasAgent->mainModules($module);
    }

    public function enabledModules($module = null)
    {
        return $this->saasAgent->enabledModules($module);
    }

    public function dataConfig($module = null): array
    {
        return $this->saasAgent->dataConfig($module);
    }

    public function configService(): ConfigService
    {
        return $this->configService;
    }

    public function moduleConfig($key = null): array
    {
        return $this->saasAgent->moduleConfig($key);
    }

    public function connection(): array
    {
        return $this->saasAgent->connection();
    }

    public function plugin(): array
    {
        return $this->saasAgent->plugin();
    }

    public function pluginId(): string
    {
        return $this->saasAgent->plugin()['id'];
    }
}
