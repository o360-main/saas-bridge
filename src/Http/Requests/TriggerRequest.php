<?php

namespace O360Main\SaasBridge\Http\Requests;

use Log;
use O360Main\SaasBridge\Contracts\BaseRequest;
use O360Main\SaasBridge\Module;
use O360Main\SaasBridge\ModuleAction;

class TriggerRequest extends BaseRequest
{
    public function rules(): array
    {
        //for 2.1

        // $version = config('saas-bridge.main_version');
        // Log::error('version:xx ' . $version);
        // Log::error("request: ", $this->all());

        if ($this->manifestVersion() == 'v1') {
            return [
                'payload' => 'array',
                'action' => 'required|string',
            ];
        }

        return [
            'payload' => 'array',
            'data' => 'array', //nullable in older version
            'action' => 'required|string',
            'module' => 'required|string', //nullable in older version
        ];
    }

    public function payload(): array
    {
        return $this->input('payload', []);
    }

    protected function manifestVersion(): string
    {
        return strtolower($this->input("_env.manifest_version", 'v2'));
    }


    public function data(): array
    {
        // $version = $this->manifestVersion();
        // if ($version == 'v1') {
        //     return $this->payload();
        // }

        return $this->input('data', []);
    }

    public function action(): ModuleAction
    {
        //        $version = config('saas-bridge.main_version');

        $action = $this->input('action');
        if (str_contains('.', $action)) {
            [, $action] = explode('.', $action);

            return ModuleAction::from($action);
        }
        //        if ($version == 'v1') {
        //            [, $action] = explode('.', $this->input('action'));
        //            return ModuleAction::from($action);
        //        }

        return ModuleAction::from($action);

    }

    public function module(): Module
    {
        $module = $this->input('module', null);

        if (is_null($module)) {
            [$module] = explode('.', $this->input('action'));

            return Module::from($module);
        }

        return Module::from($module);
    }
}
