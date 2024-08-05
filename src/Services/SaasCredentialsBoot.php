<?php

namespace O360Main\SaasBridge\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\UnauthorizedException;
use O360Main\SaasBridge\SaasAgent;
use O360Main\SaasBridge\SaasBridgeService;
use O360Main\SaasBridge\SaasConfig;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SaasCredentialsBoot
{
    private SaasAgent $saasAgent;

    private PendingRequest $saasApi;

    private ?string $cred = null;

    private array $data = [];

    private bool $validated = false;

    private SaasConfig $saasConfig;

    public static function make(Request $request): self
    {
        return new self($request);
    }

    public function __construct(Request $request)
    {
        $this->saasAgent = SaasAgent::getInstance();

        $this->saasConfig = SaasConfig::getInstance();

        $encryptedData = $request->input('_env._data') ?? null;

        if (! $encryptedData) {
            throw new UnauthorizedException('Invalid Payload');
        }

        $isWorked = EncryptionCall::decrypt($encryptedData, $this->saasConfig->secret());

        if (! $isWorked) {
            throw new UnauthorizedException('Invalid Payload');
        }

        $this->data = json_decode($isWorked, true);

        $request->request->remove('_env._data');

    }

    public static function setEnvironment(Request $request): void
    {
        $environment = $request->input('_env', []);

        if (empty($environment)) {
            abort(401, 'Environment not set');
        }

        \config()->set('saas-bridge.main_version', $environment['version'] ?? '1.0.0');
        \config()->set('saas-bridge.plugin_dev', $environment['dev_mode'] ?? false);
        \config()->set('saas-bridge.debug', $environment['debug'] ?? false);
        \config()->set('saas-bridge.core_url', $environment['core_url'] ?? null);
        //        \config()->set('saas-bridge.pass_headers', $environment['pass_headers'] ?? []);

    }

    public static function validateJwt(Request $request): void
    {
        $JwtToken = $request->bearerToken();

        $pluginSecret = SaasConfig::getInstance()->secret();

        $pluginSecret = base64_decode($pluginSecret);

        if (! $JwtToken) {
            throw new UnauthorizedException('Invalid Access Key | 0');
        }

        if (! EncryptionCall::validateJwtToken($JwtToken, $pluginSecret)) {
            throw new UnauthorizedException('Invalid Access Key | 1');
        }
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
        $config = $this->saasConfig;

        $baseUrl = $config->coreUrl();
        $passHeaders = $this->data['_pass_headers'] ?? [];
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            ...$passHeaders,
        ];

        $this->saasApi = Http::baseUrl($baseUrl)->withHeaders($headers);

        $this->saasAgent->setSaasApi($this->saasApi);
    }

    /**
     * @throws \Exception
     */
    private function validate(): void
    {

        $config = SaasConfig::getInstance();

        if ($this->validated) {
            return;
        }

        if ($config->secret() === null) {
            throw new AccessDeniedHttpException('Invalid Plugin Secret');
        }

        $data = $this->data['_cred'] ?? [];
        $data['main_modules'] = $data['source'] ?? [];

        $this->saasAgent->setConnection($data['connection'] ?? []);
        $this->saasAgent->setCredentials($data['config'] ?? []);
        $this->saasAgent->setModuleConfig($data['module_config'] ?? []);
        $this->saasAgent->setPlugin($data['plugin'] ?? []);
        $this->saasAgent->setDataConfig($data['data_config'] ?? []);
        $this->saasAgent->setSource($data['source'] ?? $data['source_of_truth'] ?? $data['main_modules'] ?? []);
        $this->saasAgent->setEnabled($data['enabled_modules'] ?? []);
    }
}
