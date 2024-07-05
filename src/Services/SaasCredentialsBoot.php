<?php

namespace O360Main\SaasBridge\Services;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
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
    private ?string $cred = null;


    private bool $validated = false;

    private array $environmentVariables = [];

    public function __construct(Request $request)
    {

        $this->environmentVariables = [
            'core_url' => config('saas-bridge.saas_api_url'),
            'dev_mode' => config('saas-bridge.plugin_dev', false),
            'secret' => config('saas-bridge.plugin_secret'),
//            'debug' => false,
            'main_version' => config('saas-bridge.main_version', 'v1'),
        ];

        //        $this->saasAgent = app(SaasAgent::class);
        $this->saasAgent = SaasAgent::getInstance();

        $this->request = $request;


        $all = $request->all() ?? [];

        $this->environment = $all['_env'] ?? [];

        $this->cred = $all['_cred'] ?? null;

        $this->auth['token'] = $this->request->bearerToken();
    }


    public static function validateJwt(Request $request): void
    {
        $JwtToken = $request->bearerToken();
        $pluginSecret = config('saas-bridge.plugin_secret');

        //check token is exists on header
        if (!$JwtToken) {
            throw new UnauthorizedException('Invalid Access Key | 0');
        }

        if (!EncryptionCall::validateJwtToken($JwtToken, $pluginSecret)) {
            throw new UnauthorizedException('Invalid Access Key | 1');
        }
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


        $JwtToken = $this->auth['token'] ?? null;
        $pluginSecret = $this->environmentVariables['secret'] ?? null;

        $XToken = $this->request->header('X-Token');

        //check token is exists on header
        if (!$JwtToken) {
            throw new UnauthorizedException('Invalid Access Key | 0');
        }

        if ($this->environment['version'] != "1.0.0") {
            if (!EncryptionCall::validateJwtToken($this->auth['token'], $this->environmentVariables['secret'])) {
                throw new UnauthorizedException('Invalid Access Key | 1');
            }
        }


        $baseUrl = $this->environment['core_url'] ?? $this->environmentVariables['core_url'];
        $devMode = $this->environment['dev_mode'] ?? $this->environmentVariables['dev_mode'];
        $debug = $this->environment['debug'] ?? false;
        $mainVersion = $this->environment['version'] ?? $this->environmentVariables['main_version'];

        if (empty($baseUrl)) {
            throw new Exception('CoreApi url not set');
        }

        $headers = [];

        //check plugin id is existing on header
        $pluginId = $this->request->header('X-Plugin-Id');

        if (!$pluginId && $this->environment['version'] == "1.0.0") {
            throw new UnauthorizedException('Plugin id not found');
        }

        $headers['X-Plugin-Id'] = $pluginId;
        Config::set('saas-bridge.plugin_id', $pluginId);

        //check is in dev mode
        $headers['X-Plugin-Dev'] = $devMode;

//        "2.0.0"
        $headers['X-Main-Version'] = $this->environment['version'] ?? "1.0.0";

        //Set headers
        $headers = [
            'Authorization' => 'Bearer ' . $XToken,
            'X-Pass-Token' => $JwtToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
//            'X-Plugin-Secret' => $pluginSecret,
            ...$headers,
        ];

        $this->saasApi = Http::baseUrl($baseUrl)->withHeaders($headers);

        $this->saasAgent->setSaasApi($this->saasApi, $this->environment);
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
        if ($this->cred != null) {
            $data = EncryptionCall::decrypt($this->cred, $this->environmentVariables['secret']);

            if (!$data) {
                throw new AccessDeniedHttpException('Invalid Access Key || Invalid Plugin Secret');
            }

            $data = json_decode($data, true);
            $data['main_modules'] = $data['source'] ?? [];

        } else {
            $response = $this->saasApi->get(
                config('saas-bridge.token_validate_endpoint', "/connection/validate?from=" . $this->environment['version']),
            );

            if (!$response->ok()) {
                throw new AccessDeniedHttpException('Invalid Access Key');
            }

            $this->validated = true;

            $data = $response->json();
        }

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
