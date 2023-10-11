<?php

namespace O360Main\SaasBridge\Http\Requests;

use O360Main\SaasBridge\Contracts\BaseRequest;

class TriggerRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
        return $this->payload(); //for now payload as data
    }


    public function action(): string
    {
        return strtolower($this->input('action', ''));
    }
}
