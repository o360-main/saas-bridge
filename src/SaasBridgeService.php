<?php

namespace O360Main\SaasBridge;

class SaasBridgeService
{
    public function __construct(private readonly SaasAgent $saasAgent)
    {
    }

    public function getInstance(): self
    {
        return $this;
    }

    // Build your next great package.
    public function api($version = null): \Illuminate\Http\Client\PendingRequest
    {
        return $this->saasAgent->saasApi($version);
    }

    public function credentials(): array
    {
        return $this->saasAgent->credentials();
    }

    public function configurations(): array
    {
        return $this->saasAgent->moduleConfig();
    }

}
