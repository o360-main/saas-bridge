<?php

namespace O360Main\SaasBridge\Responses;

use Illuminate\Contracts\Support\Responsable;

class ConfigResponse implements Responsable
{
    public function __construct(
        public readonly bool  $available = false,
        public readonly bool  $webhook = false,

        public readonly array $form_fields = [],
    )
    {
    }

    public function toResponse($request): array
    {
        return [
            'available' => $this->available,
            'webhook' => $this->webhook,
            'form_fields' => $this->form_fields,
        ];
    }
}
