<?php

namespace O360Main\SaasBridge\Config;

use Illuminate\Contracts\Support\Arrayable;

class FormFieldOption implements Arrayable
{
    public function __construct(
        protected string           $label,
        protected string|int|float $value,
    ) {
    }

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'value' => $this->value,
        ];
    }
}
