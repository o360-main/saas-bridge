<?php

namespace O360Main\SaasBridge\Http\Responses\Manifest;

enum PluginType: string
{
    case ECOMMERCE = 'ecom';
    case POS = 'pos';
    case ERP = 'erp';
}
