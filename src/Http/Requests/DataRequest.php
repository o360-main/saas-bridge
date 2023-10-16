<?php

namespace O360Main\SaasBridge\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use O360Main\SaasBridge\Contracts\BaseRequest;

class DataRequest extends FormRequest
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
            //
        ];
    }


}
