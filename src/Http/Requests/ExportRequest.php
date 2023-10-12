<?php

namespace O360Main\SaasBridge\Http\Requests;

use O360Main\SaasBridge\Contracts\BaseRequest;
use O360Main\SaasBridge\ModuleEvent;

class ExportRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'payload' => 'required|array',
            'payload.id' => 'required|string',
            'event' => 'required|string',
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
        $str = event_action_extract($this->input('event'));

        return ModuleEvent::from($str);
    }

}
