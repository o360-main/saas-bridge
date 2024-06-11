<?php

namespace O360Main\SaasBridge;

enum Module: string
{
    //Simple
    case category = "category";
    case store = "store";
    case attribute = "attribute";
    case tax = "tax";
    case currency = "currency";
    case paymentMethod = "payment_method";
    case tierGroup = "tier_group";

    //Complex
    case customer = "customer";
    case product = "product";
    case inventory = "inventory";
    case order = "order";
    case seller = "seller";
    case account = "account";

    case discount = 'discount';
    case gift_card = 'gift_card';

    // Tier Price
    case tier_price = 'tier_price';

    case errorLog = "error_log";
    case pluginLog = "plugin_log";

    /**
     * @throws \Exception
     */
    public function detail(string $key = null): array|string|bool
    {
        $value = match ($this) {
            self::category => [
                'name' => 'category',
                'plural' => 'categories',
                'label' => 'Category',
                'label_plural' => 'Categories',
                'simple' => true,
            ],

            self::store => [
                'name' => 'store',
                'plural' => 'stores',

                'label' => 'Store',
                'label_plural' => 'Stores',
                'simple' => true,
            ],

            self::attribute => [
                'name' => 'attribute',
                'plural' => 'attributes',
                'label' => 'Attribute',
                'label_plural' => 'Attributes',
                'simple' => true,
            ],

            self::tax => [
                'name' => 'tax',
                'plural' => 'taxes',
                'label' => 'Tax',
                'label_plural' => 'Taxes',
                'simple' => true,
            ],

            self::currency => [
                'name' => 'currency',
                'plural' => 'currencies',
                'label' => 'Currency',
                'label_plural' => 'Currencies',
                'simple' => true,
            ],

            self::paymentMethod => [
                'name' => 'payment_method',
                'plural' => 'payment_methods',
                'label' => 'PaymentMethod',
                'label_plural' => 'PaymentMethods',
                'simple' => true,
            ],

            self::tierGroup => [
                'name' => 'tier_group',
                'plural' => 'tier_groups',

                'label' => 'TierGroup',
                'label_plural' => 'TierGroups',
                'simple' => true,
            ],

            self::customer => [
                'name' => 'customer',
                'plural' => 'customers',
                'label' => 'Customer',
                'label_plural' => 'Customers',
                'simple' => false,
            ],

            self::product => [
                'name' => 'product',
                'plural' => 'products',
                'label' => 'Product',
                'label_plural' => 'Products',
                'simple' => false,
            ],

            self::inventory => [
                'name' => 'inventory',
                'plural' => 'inventories',
                'label' => 'Inventory',
                'label_plural' => 'Inventories',
                'simple' => false,
            ],

            self::order => [
                'name' => 'order',
                'plural' => 'orders',
                'label' => 'Order',
                'label_plural' => 'Orders',
                'simple' => false,
            ],

            self::seller => [
                'name' => 'seller',
                'plural' => 'sellers',
                'label' => 'Seller',
                'label_plural' => 'Sellers',
                'simple' => false,
            ],

            self::account => [
                'name' => 'account',
                'plural' => 'accounts',
                'label' => 'Account',
                'label_plural' => 'Accounts',
                'simple' => false,
            ],

            self::discount => [
                'name' => 'discount',
                'plural' => 'discounts',
                'label' => 'Discount',
                'label_plural' => 'Discounts',
                'simple' => false,
            ],

            self::gift_card => [
                'name' => 'gift_card',
                'plural' => 'gift_cards',
                'label' => 'GiftCard',
                'label_plural' => 'GiftCards',
                'simple' => false,
            ],

            self::tier_price => [
                'name' => 'tier_price',
                'plural' => 'tier_prices',
                'label' => 'TierPrice',
                'label_plural' => 'TierPrices',
                'simple' => false,
            ],

            self::errorLog => [
                'name' => 'error_log',
                'plural' => 'error_logs',
                'label' => 'ErrorLog',
                'label_plural' => 'ErrorLogs',
                'simple' => true,
            ],
            self::pluginLog => [
                'name' => 'plugin_log',
                'plural' => 'plugin_logs',
                'label' => 'PluginLog',
                'label_plural' => 'PluginLogs',
                'simple' => false,
            ],


            default => throw new \Exception("Invalid Module")

        };

        return $key ? $value[$key] : $value;
    }

    /**
     * @throws \Exception
     */
    public function plural(): string
    {
        return $this->detail('plural');
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isSimple(): bool
    {
        return $this->detail('simple');
    }

    /**
     * @throws \Exception
     */
    public function event(ModuleEvent $event): string
    {
        return $this->name . ' . ' . $event->value;
    }


    public function str(string $append): string
    {
        return $this->name . ' . ' . $append;
    }
}
