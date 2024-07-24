<?php

namespace O360Main\SaasBridge\Services;

class SignatureService
{
    private string $salt = "o360-solutions";
    private EncService $encService;

    public function __construct()
    {
        $this->salt = config('saas-bridge.plugin_secret');
        $this->encService = EncService::getInstance();
    }

    public function generateSignature(): string
    {
        return $this->encService->sign($this->salt);
    }

    public function verifySignature(string $signature): bool
    {
        return $this->encService->verify($this->salt, $signature);
    }

}
