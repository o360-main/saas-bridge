<?php

namespace O360Main\SaasBridge\Http\Requests;

use O360Main\SaasBridge\Contracts\BaseRequest;
use O360Main\SaasBridge\Module;
use O360Main\SaasBridge\ModuleEvent;

class ImportRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'payload' => 'array',
            'event' => 'required|string',
        ];
    }

    public function payload(): array
    {
        return $this->input('payload', []);
    }

    public function event(): ModuleEvent
    {
        $version = config('saas-bridge.main_version');

        if ($version == 'v1') {
            [, $event] = explode('.', $this->input('action'));
            return ModuleEvent::from($event);
        }

        return ModuleEvent::from($this->input('payload.body.event'));
    }

    public function module(): Module
    {
        $module = $this->input('module', null);

        if (is_null($module)) {
            [$module,] = explode('.', $this->input('event'));

            return Module::from($module);
        }

        return Module::from($module);
    }

}
