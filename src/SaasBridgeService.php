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
    public function api(): \Illuminate\Http\Client\PendingRequest
    {
        return $this->saasAgent->saasApi();
    }

    public function credentials(): array
    {
        return $this->saasAgent->credentials();
    }

    public function configurations()
    {
        return $this->saasAgent->moduleConfig();
    }

}
