<?php

namespace O360Main\SaasBridge\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use O360Main\SaasBridge\Config\FormField;

class ConfigResponse implements Responsable
{
    /**
     * @throws \Exception
     */
    public function __construct(
        public readonly bool  $available = false,
        public readonly bool  $webhook = false,
        public readonly array $form_fields = [],
    ) {

        foreach ($this->form_fields as $form_field) {

            if (!$form_field instanceof FormField) {
                throw new \Exception('Invalid form field');
            }
        }
    }

    public function toResponse($request): array
    {
        return [
            'available' => $this->available,
            'webhook' => $this->webhook,
            'form_fields' => collect($this->form_fields ?? [])->map(fn ($form_field) => $form_field->toArray())->toArray(),
        ];
    }
}
