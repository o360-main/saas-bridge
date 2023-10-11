<?php

namespace O360Main\SaasBridge\Http\Requests;

use O360Main\SaasBridge\Contracts\BaseRequest;

class ExportRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required',
        ];
    }

    public function saasId()
    {
        return $this->input('id');
    }


    public function payload()
    {
        return $this->input('payload', []);
    }


    public function event(): string
    {
        return strtolower($this->input('event', ''));
    }


}
