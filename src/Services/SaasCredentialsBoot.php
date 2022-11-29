<?php

namespace O360Main\SaasBridge\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use O360Main\SaasBridge\SaasAgent;

class SaasCredentialsBoot
{

    /**
     * @var \Illuminate\Http\Request
     */
    private Request $request;
    private array $auth = [];

    private SaasAgent $saasAgent;


    public function __construct(Request $request)
    {
//        $this->saasAgent = app(SaasAgent::class);
        $this->saasAgent = SaasAgent::getInstance();

        $this->request = $request;

        $this->auth['token'] = $this->request->bearerToken();
    }


    /**
     * @throws \Exception
     */
    public function run()
    {
        //todo initiate token and http client
        $this->initSaasApi();

        // todo validate token and parse url info
        $this->validate();

        //todo other info
    }

    /**
     * @throws \Exception
     */
    private function initSaasApi()
    {
        if (!isset($this->auth['token'])) {
            throw new \Exception('Token not found');
        }

        $baseUrl = config('saas_bridge.core_url');

        $client = Http::baseUrl($baseUrl)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->auth['token'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);

        $this->saasAgent->setSaasApi($client);
    }

    /**
     * @throws \Exception
     */
    private function validate(): void
    {
        if (rand(0, 1) === 0) {
            throw new \Exception('Unauthorized');
        }
    }


}