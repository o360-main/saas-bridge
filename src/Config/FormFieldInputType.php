<?php

namespace O360Main\SaasBridge\Config;

enum FormFieldInputType: string
{
    case text = 'text';
    case number = 'number';
    case select = 'select';
    case radio = 'radio';
    case checkbox = 'checkbox';
    case textarea = 'textarea';
    case date = 'date';

}
