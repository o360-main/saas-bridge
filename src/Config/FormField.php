<?php

namespace O360Main\SaasBridge\Config;

class FormField
{

    /**
     * @throws \Exception
     */
    public function __construct(
        protected string             $name,
        protected string             $label,
        protected FormFieldInputType $input_type,
        protected FormFieldDataType  $type,
        protected string             $placeholder,
        protected string             $description,
        protected array              $options = [],
        protected ?string            $pattern = null,
        protected ?string            $default_value = null,
        protected int                $index = 0,
        protected bool               $required = true,
        protected bool               $multiple = false,
    )
    {
        foreach ($this->options as $option) {

            if (!$option instanceof FormFieldOption) {
                throw new \Exception('Options must be instance of FormFieldOption');
            }
        }
    }


    /**
     * @throws \Exception
     */
    public static function make(
        string             $name,
        string             $label,
        FormFieldInputType $input_type,
        FormFieldDataType  $type,
        string             $placeholder,
        string             $description,
        array              $options = [],
        ?string            $pattern = null,
        ?string            $default_value = null,
        int                $index = 0,
        bool               $required = true,
        bool               $multiple = false,
    ): self
    {

        return new self(
            name: $name,
            label: $label,
            input_type: $input_type,
            type: $type,
            placeholder: $placeholder,
            description: $description,
            options: $options,
            pattern: $pattern,
            default_value: $default_value,
            index: $index,
            required: $required,
            multiple: $multiple
        );

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
            'options' => $this->options,
            'pattern' => $this->pattern
        ];
    }

}
