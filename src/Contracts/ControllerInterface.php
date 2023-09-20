<?php

namespace O360Main\SaasBridge\Contracts;

use Illuminate\Http\Request;

interface ControllerInterface
{
    //Routes : /api/{module}/data
    public function data(Request $request): array;

    //Routes : /api/{module}/config
    public function config(Request $request): array;

    //Routes : /api/{module}/import
    public function import(Request $request);

    //Routes : /api/{module}/export
    public function export(Request $request);

    public function trigger(Request $request);
}
