<?php

namespace O360Main\SaasBridge\Services;

use Config;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\UnauthorizedException;
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

    private array $environment = [];


    private bool $validated = false;

    public function __construct(Request $request)
    {
        //        $this->saasAgent = app(SaasAgent::class);
        $this->saasAgent = SaasAgent::getInstance();

        $this->request = $request;

        $env = $request->input('_env', []);

        if (!empty($env)) {
            $env = $request->input('env', []);
        }

        $this->environment = $env;

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
        //check token is exists on header
        if (!isset($this->auth['token'])) {
            throw new UnauthorizedException('Invalid Access Key');
        }


        Log::error("ENVIRONMENT", [
            'env' => $this->environment,
            'all' => $this->request->all(),
        ]);

        $baseUrl = $this->environment['core_url'] ?? config('saas-bridge.saas_api_url');
        $devMode = $this->environment['dev_mode'] ?? config('saas-bridge.plugin_dev', false);
        $debug = $this->environment['debug'] ?? false;
        $mainVersion = $this->environment['version'] ?? config('saas-bridge.main_version', 'v1');

        if (empty($baseUrl)) {
            throw new Exception('CoreApi url not set');
        }

        $headers = [];

        //check plugin id is existing on header
        $pluginId = $this->request->header('X-Plugin-Id');
        if (!$pluginId) {
            throw new UnauthorizedException('Plugin id not found');
        }

        $headers['X-Plugin-Id'] = $pluginId;
        Config::set('saas-bridge.plugin_id', $pluginId);

        //check is in dev mode
        $headers['X-Plugin-Dev'] = $devMode;
        $headers['X-Main-Version'] = $mainVersion;

        //Set headers
        $headers = [
            'Authorization' => 'Bearer ' . $this->auth['token'],
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Plugin-Secret' => config('saas-bridge.plugin_secret'),
            ...$headers,
        ];

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

        if (!$response->ok()) {
            throw new AccessDeniedHttpException('Invalid Access Key');
        }

        $this->validated = true;

        $data = $response->json();

        //
        //        'connection' => $this->connection,
        //            'config' => $this->config,
        //            'module_config' => $this->moduleConfig,
        //            'plugin' => $this->plugin,
        //            'source' => $this->source,
        //            'main_modules' => $this->source,
        //            'enabled_modules' => $this->enabled_modules,
        //            'data_config' => $this->dataConfig,

        $this->saasAgent->setConnection($data['connection'] ?? []);
        $this->saasAgent->setCredentials($data['config'] ?? []);
        $this->saasAgent->setModuleConfig($data['module_config'] ?? []);
        $this->saasAgent->setPlugin($data['plugin'] ?? []);
        $this->saasAgent->setDataConfig($data['data_config'] ?? []);
        $this->saasAgent->setSource($data['source'] ?? $data['source_of_truth'] ?? $data["main_modules"] ?? []);
        $this->saasAgent->setEnabled($data['enabled_modules'] ?? []);
    }
}
