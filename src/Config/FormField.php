<?php

namespace O360Main\SaasBridge\Config;

class FormField
{

    /**
     * @throws \Exception
     */
    public function __construct(
        protected FormFieldInputType $input_type,
        protected FormFieldDataType  $type,
        protected string             $name,
        protected string             $label,
        protected string             $placeholder,
        protected string             $description,
        protected ?array             $options = [],
        protected ?string            $default_value = null,
        protected int                $index = 0,
        protected bool               $required = true,
        protected bool               $multiple = false,
        protected ?string            $pattern = null,
    )
    {

        foreach ($this->options as $option) {

            if (!$option instanceof FormFieldOption) {
                throw new \Exception('Options must be instance of FormFieldOption');
            }
        }
    }



    public function toArray(): array
    {
        return [
            'index' => $this->index,
            'name' => $this->name,
            'input_type' => $this->input_type,
            'type' => $this->type,
            'label' => $this->label,
            'default_value' => $this->default_value,
            'required' => $this->required,
            'multiple' => $this->multiple,
            'placeholder' => $this->placeholder,
            'description' => $this->description,
            'options' => collect($this->options ?? [])->map(fn($option) => $option->toArray())->toArray(),
            'pattern' => $this->pattern
        ];
    }

}
