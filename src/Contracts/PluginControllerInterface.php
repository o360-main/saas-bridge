<?php

namespace O360Main\SaasBridge\Contracts;

use Illuminate\Http\JsonResponse;
use O360Main\SaasBridge\Http\Responses\ManifestResponse;
use O360Main\SaasBridge\Http\Responses\PingResponse;
use O360Main\SaasBridge\Http\Responses\TestCredentialsResponse;

interface PluginControllerInterface
{

    public function manifest(): ManifestResponse;

    public function ping(): PingResponse;

    public function testCredentials(): TestCredentialsResponse;

}
