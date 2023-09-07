<?php

namespace O360Main\SaasBridge\Config;

class FormField
{
    public static function make(
        $name,
        $label,
        $input_type,
        $type,
        $default_value = null,
        $index = 0,
        $required = true,
        $multiple = false,
        $placeholder = null,
        $description = null,
        $options = [],
        $pattern = null
    ): array {
        return compact(
            'index',
            'name',
            'input_type',
            'type',
            'label',
            'default_value',
            'required',
            'multiple',
            'placeholder',
            'description',
            'options',
            'pattern'
        );
    }


    public static function text(
        $name,
        $label,
        $default_value = null,
        $index = 0,
        $required = true,
        $placeholder = null,
        $description = null,
        $pattern = null,
    ): array {
        return self::make(
            $name,
            $label,
            'text',
            'string',
            $default_value,
            $index,
            $required,
            false,
            $placeholder,
            $description,
            [],
            $pattern
        );
    }


    public static function select(
        $name,
        $label,
        $options,
        $default_value = null,
        $index = 0,
        $required = true,
        $placeholder = null,
        $description = null,
        $multiple = false
    ): array {
        return self::make(
            $name,
            $label,
            'select',
            'string',
            $default_value,
            $index,
            $required,
            $multiple,
            $placeholder,
            $description,
            $options
        );
    }


    public static function checkbox(
        $name,
        $label,
        $default_value = null,
        $index = 0,
        $required = true,
        $placeholder = null,
        $description = null,
        $multiple = false
    ): array {
        return self::make(
            $name,
            $label,
            'checkbox',
            'boolean',
            $default_value,
            $index,
            $required,
            $multiple,
            $placeholder,
            $description
        );
    }


    public static function number(
        $name,
        $label,
        $default_value = null,
        $index = 0,
        $required = true,
        $placeholder = null,
        $description = null,
    ): array {
        return self::make(
            $name,
            $label,
            'number',
            'integer',
            $default_value,
            $index,
            $required,
            false,
            $placeholder,
            $description
        );
    }


    public static function date(
        $name,
        $label,
        $default_value = null,
        $index = 0,
        $required = true,
        $placeholder = null,
        $description = null
    ): array {
        return self::make(
            $name,
            $label,
            'date',
            'date',
            $default_value,
            $index,
            $required,
            false,
            $placeholder,
            $description
        );
    }


    public static function datetime(
        $name,
        $label,
        $default_value = null,
        $index = 0,
        $required = true,
        $placeholder = null,
        $description = null,
    ): array {

        return self::make(
            $name,
            $label,
            'datetime',
            'datetime',
            $default_value,
            $index,
            $required,
            false,
            $placeholder,
            $description
        );
    }


    public static function time(
        $name,
        $label,
        $default_value = null,
        $index = 0,
        $required = true,
        $placeholder = null,
        $description = null
    ): array {
        return self::make(
            $name,
            $label,
            'time',
            'time',
            $default_value,
            $index,
            $required,
            false,
            $placeholder,
            $description
        );
    }


    public static function textarea(
        $name,
        $label,
        $default_value = null,
        $index = 0,
        $required = true,
        $placeholder = null,
        $description = null
    ): array {
        return self::make(
            $name,
            $label,
            'textarea',
            'string',
            $default_value,
            $index,
            $required,
            false,
            $placeholder,
            $description
        );
    }


    public static function email(
        $name,
        $label,
        $default_value = null,
        $index = 0,
        $required = true,
        $placeholder = null,
        $description = null,
    ): array {
        return self::make(
            $name,
            $label,
            'email',
            'string',
            $default_value,
            $index,
            $required,
            false,
            $placeholder,
            $description
        );
    }


    public static function password(
        $name,
        $label,
        $default_value = null,
        $index = 0,
        $required = true,
        $placeholder = null,
        $description = null,
        $pattern = null,
    ): array {
        return self::make(
            $name,
            $label,
            'password',
            'string',
            $default_value,
            $index,
            $required,
            false,
            $placeholder,
            $description,
            [],
            $pattern
        );
    }


    public static function file(
        $name,
        $label,
        $default_value = null,
        $index = 0,
        $required = true,
        $placeholder = null,
        $description = null,
        $multiple = false
    ): array {
        return self::make(
            $name,
            $label,
            'file',
            'string',
            $default_value,
            $index,
            $required,
            $multiple,
            $placeholder,
            $description
        );
    }


    public static function image(
        $name,
        $label,
        $default_value = null,
        $index = 0,
        $required = true,
        $placeholder = null,
        $description = null,
        $multiple = false
    ): array {
        return self::make(
            $name,
            $label,
            'image',
            'string',
            $default_value,
            $index,
            $required,
            $multiple,
            $placeholder,
            $description
        );
    }


    public static function color(
        $name,
        $label,
        $default_value = null,
        $index = 0,
        $required = true,
        $placeholder = null,
        $description = null,
        $pattern = null,
    ): array {
        return self::make(
            $name,
            $label,
            'color',
            'string',
            $default_value,
            $index,
            $required,
            false,
            $placeholder,
            $description,
            [],
            $pattern
        );
    }


    public static function url(
        $name,
        $label,
        $default_value = null,
        $index = 0,
        $required = true,
        $placeholder = null,
        $description = null,
        $pattern = null,
    ): array {
        return self::make(
            $name,
            $label,
            'url',
            'string',
            $default_value,
            $index,
            $required,
            false,
            $placeholder,
            $description,
            [],
            $pattern
        );
    }


    public static function tel(
        $name,
        $label,
        $default_value = null,
        $index = 0,
        $required = true,
        $placeholder = null,
        $description = null,
        $pattern = null,
    ): array {
        return self::make(
            $name,
            $label,
            'tel',
            'string',
            $default_value,
            $index,
            $required,
            false,
            $placeholder,
            $description,
            [],
            $pattern
        );
    }


//    public static function range($name, $label, $min, $max, $default_value = null, $index = 0, $required = true, $placeholder = null, $description = null): array
//    {
//        $options = compact('min', 'max');
//        return self::make($name, $label, 'range', 'integer', $default_value, $index, $required, $placeholder, $description, $options);
//    }


}
