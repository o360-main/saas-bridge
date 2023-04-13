<?php

namespace O360Main\SaasBridge\Contracts;

use Illuminate\Http\Request;
use O360Main\SaasBridge\SaasAgent;

interface ControllerInterface
{
    public function jsonSample(Request $request, SaasAgent $saasAgent);

    public function import(Request $request, SaasAgent $saasAgent);

    public function export(Request $request, SaasAgent $saasAgent);

    public function trigger(Request $request, SaasAgent $saasAgent);
}
