<?php

namespace O360Main\SaasBridge\Config;

enum FormFieldDataType: string
{
    case string = 'string';
    case number = 'integer';
    case float = 'float';
    case boolean = 'boolean';
    case array = 'array';
    case object = 'object';
    case date = 'date';
}
