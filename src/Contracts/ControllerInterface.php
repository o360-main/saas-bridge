<?php

namespace O360Main\SaasBridge\Contracts;

use O360Main\SaasBridge\Http\Requests\ConfigRequest;
use O360Main\SaasBridge\Http\Requests\DataRequest;
use O360Main\SaasBridge\Http\Requests\ExportRequest;
use O360Main\SaasBridge\Http\Requests\ImportRequest;
use O360Main\SaasBridge\Http\Requests\TriggerRequest;
use O360Main\SaasBridge\Http\Responses\ConfigResponse;
use O360Main\SaasBridge\Http\Responses\DataResponse;
use O360Main\SaasBridge\Http\Responses\ExportResponse;
use O360Main\SaasBridge\Http\Responses\ImportResponse;
use O360Main\SaasBridge\Http\Responses\TriggerResponse;

interface ControllerInterface
{
    //Routes : /api/{module}/data
    public function data(DataRequest $request): DataResponse;

    //Routes : /api/{module}/config
    public function config(ConfigRequest $request): ConfigResponse;

    //Routes : /api/{module}/import
    public function import(ImportRequest $request): ImportResponse;

    //Routes : /api/{module}/export
    public function export(ExportRequest $request): ExportResponse;

    public function trigger(TriggerRequest $request): TriggerResponse;
}
