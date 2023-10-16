<?php

namespace O360Main\SaasBridge\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use O360Main\SaasBridge\ModuleAction;

class TriggerRequest extends FormRequest
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
            'action' => 'required|string',
        ];
    }


    /**
     * Extra Functions
     */

    public function payload(): array
    {
        return $this->input('payload', []);
    }

    public function data(): array
    {

        $version = config('saas-bridge.version');

        if ($version == 'v1') {
            return $this->payload();
        }

        return $this->payload()['data'] ?? [];
    }


    public function action(): ModuleAction
    {

        [$module, $action] = explode('.', $this->input('action'));

        return ModuleAction::from($action);
    }

}
