<?php

namespace O360Main\SaasBridge\ApiClient;

enum Endpoint: string
{
    case currency = 'currencies';
    case store = 'stores';
    case tax = 'taxes';
    case attribute = 'attributes';
    case category = 'categories';
    case payment_method = 'payment-methods';
    case tier_group = 'tier-groups';
    # Advance
    case product = 'products';
    case inventory = 'inventories';
    case customer = 'customers';
    case seller = 'sellers';
    case order = 'orders';

}
