<?php

namespace O360Main\SaasBridge;

class SaasConfig
{
    private static self|null $instance = null;
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
    }

    public function secret()
    {
        return config('saas-bridge.plugin_secret', null);
    }

    public function version(): string
    {
        return config('saas-bridge.main_version', '1.0.0');
    }


    public function passHeaders(): array
    {
        return config('saas-bridge.pass_headers', []);
    }

    public function versionGreaterThenEqual($version): bool|int
    {
        return version_compare($this->version(), $version, '>=');
    }

    public function versionLessThen($version): bool|int
    {
        return version_compare($this->version(), $version, '<');
    }

    public function pluginDev(): bool
    {
        return config('saas-bridge.plugin_dev', false);
    }

    public function debug(): bool
    {
        return config('saas-bridge.debug', false);
    }

    public function coreUrl(): ?string
    {
        return config('saas-bridge.core_url', null);
    }

    public function makeCoreApi(...$args): string
    {
        //add in array at top
        array_unshift($args, $this->coreUrl());
        $arr = array_map(function ($arg) {
            return trim($arg, '/');
        }, $args);

        return implode('/', $arr);
    }
}
