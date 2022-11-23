<?php

namespace O360Main\SaasBridge;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class SaasAgent
{
    private array $auth = [];
    private $platform;

    private PendingRequest $client;

    public function setAuth(array $auth): self
    {
        $this->auth = $auth;

        $this->client = Http::withToken($this->auth['token'])
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);

        return $this;
    }

    public function setPlatform($platform): self
    {
        $this->platform = $platform;
        return $this;
    }

    public function validate(): bool
    {
        return true;
    }

    public function client(): PendingRequest
    {
        return $this->client;
    }

}
