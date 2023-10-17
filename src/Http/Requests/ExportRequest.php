<?php

namespace O360Main\SaasBridge\Http\Requests;

use O360Main\SaasBridge\Contracts\BaseRequest;
use O360Main\SaasBridge\Module;
use O360Main\SaasBridge\ModuleEvent;

class ExportRequest extends BaseRequest
{
    public function rules(): array
    {

        $version = config('saas-bridge.main_version');

        if ($version === 'v1') {
            return [
                'payload' => 'required|array',
                'payload.id' => 'required|string',
                'event' => 'required|string',
            ];
        }

        return [
            'payload' => 'required|array',
            'payload.id' => 'required|string',
            'event' => 'required|string',
            'module' => 'required|string',
        ];
    }

    public function saasId()
    {
        return $this->input('payload.id');
    }


    public function payload()
    {
        return $this->input('payload', []);
    }


    public function event(): ModuleEvent
    {
        [, $event] = explode('.', $this->input('event'));

        return ModuleEvent::from($event);
    }


    public function module(): Module
    {
        $module = $this->input('module', null);

        if (is_null($module)) {
            [$module, ] = explode('.', $this->input('event'));

            return Module::from($module);
        }

        return Module::from($module);
    }

}
