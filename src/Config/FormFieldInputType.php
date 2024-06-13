<?php

namespace O360Main\SaasBridge\Config;

enum FormFieldInputType: string
{
    case text = 'text';
    case number = 'number';
    case email = 'email';
    case password = 'password';
    case select = 'select';
    case checkbox = 'checkbox';
    case radio = 'radio';
    case textarea = 'textarea';
    case file = 'file';
    case image = 'image';
    case color = 'color';
    case date = 'date';
    case time = 'time';
    case datetime = 'datetime';
    case datetime_local = 'datetime_local';

}
