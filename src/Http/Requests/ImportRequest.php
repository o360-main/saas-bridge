<?php

namespace O360Main\SaasBridge\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use O360Main\SaasBridge\ModuleEvent;

class ImportRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }



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
