<?php

namespace O360Main\SaasBridge\Http\Requests;

use O360Main\SaasBridge\Contracts\BaseRequest;
use O360Main\SaasBridge\ModuleEvent;

class ImportRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'payload' => 'required|array',
            'event' => 'required|string',
        ];
    }

    public function payload(): array
    {
        return $this->input('payload', []);
    }

    public function event(): ModuleEvent
    {
        $str = event_action_extract($this->input('event'));

        return ModuleEvent::from($str);
    }
}
