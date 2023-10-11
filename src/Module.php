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


    /**
     * @throws \Exception
     */
    public function detail(string $key = null): array|string|bool
    {
        $value = match ($this) {
            self::category => [
                'name' => 'category',
                'label' => 'Category',
                'plural' => 'categories',
                'simple' => true,
            ],

            self::store => [
                'name' => 'store',
                'label' => 'Store',
                'plural' => 'stores',
                'simple' => true,
            ],

            self::attribute => [
                'name' => 'attribute',
                'label' => 'Attribute',
                'plural' => 'attributes',
                'simple' => true,
            ],

            self::tax => [
                'name' => 'tax',
                'label' => 'Tax',
                'plural' => 'taxes',
                'simple' => true,
            ],

            self::currency => [
                'name' => 'currency',
                'label' => 'Currency',
                'plural' => 'currencies',
                'simple' => true,
            ],

            self::paymentMethod => [
                'name' => 'payment_method',
                'label' => 'PaymentMethod',
                'plural' => 'payment_methods',
                'simple' => true,
            ],

            self::tierGroup => [
                'name' => 'tier_group',
                'label' => 'TierGroup',
                'plural' => 'tier_groups',
                'simple' => true,
            ],

            self::customer => [
                'name' => 'customer',
                'label' => 'Customer',
                'plural' => 'customers',
                'simple' => false,
            ],

            self::product => [
                'name' => 'product',
                'label' => 'Product',
                'plural' => 'products',
                'simple' => false,
            ],

            self::inventory => [
                'name' => 'inventory',
                'label' => 'Inventory',
                'plural' => 'inventories',
                'simple' => false,
            ],

            self::order => [
                'name' => 'order',
                'label' => 'Order',
                'plural' => 'orders',
                'simple' => false,
            ],

            self::seller => [
                'name' => 'seller',
                'label' => 'Seller',
                'plural' => 'sellers',
                'simple' => false,
            ],

            self::account => [
                'name' => 'account',
                'label' => 'Account',
                'plural' => 'accounts',
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
    public function event($event): string
    {
        return $this->detail('name') . '.' . $event;
    }
}
