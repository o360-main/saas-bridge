<?php

namespace O360Main\SaasBridge\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class SaasHttpClient
{
    private array $auth = [];
    private PendingRequest $client;

    /**
     * @throws \Exception
     */
    public function __construct($auth)
    {
        $this->auth = $auth;

        $this->initClient();
    }

    /**
     * @throws \Exception
     */
    private function initClient(): void
    {
        if (!isset($this->auth['token'])) {
            throw new \Exception('Token not found');
        }

        $baseUrl = config('saas-bridge.core_url');

        $this->client = Http::baseUrl($baseUrl)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->auth['token'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);
    }

    public function getClient(): PendingRequest
    {
        return $this->client;
    }

    /**
     * @throws \Exception
     */
    public function validate(): bool
    {
        if (rand(0, 1) === 0) {
            throw new \Exception('Unauthorized');
        }
        return true;
    }

}
