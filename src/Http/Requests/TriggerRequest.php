<?php

namespace O360Main\SaasBridge\Http\Requests;

use O360Main\SaasBridge\Contracts\BaseRequest;
use O360Main\SaasBridge\Module;
use O360Main\SaasBridge\ModuleAction;

class TriggerRequest extends BaseRequest
{
    public function rules(): array
    {

        $version = config('saas-bridge.main_version');

        if ($version == 'v1') {
            return [
                'payload' => 'required|array',
                'action' => 'required|string',
            ];
        }

        return [
            'payload' => 'required|array',
            'data' => 'required|array', //nullable in older version
            'action' => 'required|string',
            'module' => 'required|string' //nullable in older version
        ];
    }

    public function payload(): array
    {
        return $this->input('payload', []);
    }

    public function data(): array
    {

        $version = config('saas-bridge.main_version');

        if ($version == 'v1') {
            return $this->payload();
        }

        return $this->input('data', []);
    }


    public function action(): ModuleAction
    {

        [, $action] = explode('.', $this->input('action'));

        return ModuleAction::from($action);
    }


    public function module(): Module
    {
        $module = $this->input('module', null);

        if (is_null($module)) {
            [$module, ] = explode('.', $this->input('action'));

            return Module::from($module);
        }

        return Module::from($module);
    }

}
