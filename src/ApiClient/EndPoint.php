<?php

namespace O360Main\SaasBridge\ApiClient;

enum EndPoint: string
{
    case currency = 'currencies';
    case store = 'stores';
    case tax = 'taxes';
    case attribute = 'attributes';
    case category = 'categories';
    case payment_method = 'payment-methods';
    case catalog = 'catalogs';
    case tier_group = 'tier-groups';
    # Advance
    case product = 'products';
    case inventory = 'inventories';
    case company = 'companies';
    case customer = 'customers';
    case seller = 'sellers';
    case order = 'orders';

    case discount = 'discounts';
    case gift_card = 'gift-cards';
    case tier_price = 'tier-prices';
    case invoice = 'invoices';
    case shipping_method = 'shipping-methods';
    case product_price_class = 'product-price-classes';
    case order_item = 'order-items';
    case product_image = 'product-images';

    case error_log = 'error-logs';

    case order_return = 'order-returns';
    case order_refund = 'order-refunds';
    case order_return_item = 'order-return-items';
    case order_refund_item = 'order-refund-items';
    case order_shipping = 'order-shippings';
    case order_payment_method = 'order-payment-methods';
    case plugin_log = 'plugin-logs';
    case order_reason_code = 'order-reason-codes';
    case addresses = 'addresses';
    case cache_data = 'cache-datas';
    case loyalty_points = 'loyalty-points';

}
