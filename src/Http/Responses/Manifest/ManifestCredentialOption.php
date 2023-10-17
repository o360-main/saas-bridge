<?php

namespace O360Main\SaasBridge\Http\Responses\Manifest;

use Illuminate\Contracts\Support\Arrayable;

class ManifestCredentialOption implements Arrayable
{

    public function __construct(
        public readonly string $label,
        public readonly string $value,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'value' => $this->value,
        ];
    }
}
