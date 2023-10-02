<?php

namespace O360Main\SaasBridge\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use O360Main\SaasBridge\SaasAgent;
use O360Main\SaasBridge\SaasBridgeService;
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


    private bool $validated = false;

    public function __construct(Request $request)
    {
        //        $this->saasAgent = app(SaasAgent::class);
        $this->saasAgent = SaasAgent::getInstance();

        $this->request = $request;

        $this->auth['token'] = $this->request->bearerToken();
    }

    public static function make(Request $request): self
    {
        return new self($request);
    }


    /**
     * @throws \Exception
     */
    public function run(): void
    {
        // initiate token and http client
        $this->initSaasApi();

        //  validate token and parse url info
        $this->validate();

        // other info
        app()->singleton('saas-bridge', function () {
            return new SaasBridgeService($this->saasAgent);
        });
    }

    /**
     * @throws \Exception
     */
    private function initSaasApi(): void
    {
        if (!isset($this->auth['token'])) {
            throw new \Exception('Token not found');
        }


        $baseUrl = config('saas-bridge.saas_api_url');

        if (empty($baseUrl)) {
            throw new \Exception('CoreApi url not set');
        }



        $headers = [
            'Authorization' => 'Bearer ' . $this->auth['token'],
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            //                'X-Plugin-Secret' => $this->request->header('X-Plugin-Secret'),
            'X-Plugin-Secret' => config('saas-bridge.plugin_secret'),
            'X-Plugin-Id' => $this->request->header('X-Plugin-Id'),
        ];
        if ($this->request->header('X-Plugin-Dev')) {
            $headers['X-Plugin-Dev'] = $this->request->header('X-Plugin-Dev');
        }
        $this->saasApi = Http::baseUrl($baseUrl)->withHeaders($headers);

        $this->saasAgent->setSaasApi($this->saasApi);
    }

    /**
     * @throws \Exception
     *
     */
    private function validate(): void
    {
        if ($this->validated) {
            return;
        }

        $response = $this->saasApi->get(
            config('saas-bridge.token_validate_endpoint')
        );

//        \Log::info('xxx', [
//            'response' => $response->body(),
//            'headers' => $response->headers(),
//            'status' => $response->status(),
//        ]);

        if (!$response->ok()) {
            throw new AccessDeniedHttpException('Invalid Access Key');
        }

        $this->validated = true;

        $data = $response->json();

        $this->saasAgent->setConnection($data['connection'] ?? []);
        $this->saasAgent->setCredentials($data['config'] ?? []);
        $this->saasAgent->setModuleConfig($data['module_config'] ?? []);
        $this->saasAgent->setPlugin($data['plugin'] ?? []);
    }
}
