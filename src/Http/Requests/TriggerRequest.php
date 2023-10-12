<?php

namespace O360Main\SaasBridge\Http\Requests;

use O360Main\SaasBridge\Contracts\BaseRequest;
use O360Main\SaasBridge\ModuleAction;

class TriggerRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'payload' => 'required|array',
            'action' => 'required|string',
        ];
    }

    public function payload(): array
    {
        return $this->input('payload', []);
    }

    public function data(): array
    {
//        return $this->payload()['data'] ?? [];
        return $this->payload(); //for now payload as data - but latter it will be $payload['data']
    }


    public function action(): ModuleAction
    {
        $str = event_action_extract($this->input('action'));

        return ModuleAction::from($str);
    }


//    /**
//     * @throws \Exception
//     */
//    public function module(): Module
//    {
//        $module = $this->input('module', null);
//
//        if (is_null($module)) {
//
//            $module = collect(Module::cases())->filter(function ($module) {
//                return str_contains(request()->url(), $module->plural());
//            })->first();
//
//
//            if (is_null($module)) {
//                throw new \Exception('Module not found');
//            }
//
//        }
//
//        return $module;
//    }
}
