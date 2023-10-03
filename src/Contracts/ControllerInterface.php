<?php

namespace O360Main\SaasBridge\Contracts;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use O360Main\SaasBridge\Config\ConfigResponse;
use O360Main\SaasBridge\Config\DataResponse;
use O360Main\SaasBridge\Config\TriggerResponse;

interface ControllerInterface
{
    //Routes : /api/{module}/data
    public function data(Request $request): DataResponse;

    //Routes : /api/{module}/config
    public function config(Request $request): ConfigResponse;

    //Routes : /api/{module}/import
    public function import(Request $request);

    //Routes : /api/{module}/export
    public function export(Request $request);

    public function trigger(Request $request): TriggerResponse;
}
