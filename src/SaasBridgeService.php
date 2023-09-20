<?php

namespace O360Main\SaasBridge;

use O360Main\SaasBridge\Services\ConfigService;

class SaasBridgeService
{
    /**
     * @var \O360Main\SaasBridge\Services\ConfigService
     */
    private ConfigService $configService;

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
    public function api($version = null): \Illuminate\Http\Client\PendingRequest
    {
        return $this->saasAgent->saasApi($version);
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

    public function configService(): \O360Main\SaasBridge\Services\ConfigService
    {
        return $this->configService;
    }

    public function moduleConfig($key = null): array
    {
        return $this->saasAgent->moduleConfig($key);
    }

}
