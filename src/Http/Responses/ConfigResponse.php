<?php

namespace O360Main\SaasBridge\Http\Responses;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;

class ConfigResponse implements Responsable, Arrayable
{
    public function __construct(
        public readonly bool  $available = false,
        public readonly bool  $webhook = false,
        public readonly array $form_fields = [],
    )
    {
    }


    public function toArray(): array
    {
        return [
            'available' => $this->available,
            'webhook' => $this->webhook,
            'form_fields' => $this->form_fields,
        ];
    }


    public function toResponse($request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->toArray());
    }
}
