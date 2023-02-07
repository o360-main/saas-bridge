<?php

namespace O360Main\SaasBridge\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use O360Main\SaasBridge\SaasAgent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SaasCredentialsBoot
{
    /**
     * @var \Illuminate\Http\Request
     */
    private Request $request;
    private array $auth = [];

    private SaasAgent $saasAgent;
    private PendingRequest $saasApi;


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
    public function run(): void
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

        $baseUrl = config('saas_bridge.core_url', 'core.test');

        $this->saasApi = Http::baseUrl($baseUrl)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->auth['token'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);

        $this->saasAgent->setSaasApi($this->saasApi);
    }

    /**
     * @throws \Exception
     *
     */
    private function validate(): void
    {

        $response = $this->saasApi->get('/connection/validate');

        if ($response->status() !== 200) {
//            throw new \Exception('Invalid Access Key');
            throw new AccessDeniedHttpException('Invalid Access Key');
        }

        $this->validated = true;

        $this->saasAgent->setConfig($response->json('config'));
    }


}