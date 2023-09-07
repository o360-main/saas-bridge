<?php

namespace O360Main\SaasBridge\Stubs;

class CodeGenerate
{

    public function run()
    {

        //get all modules

        $modules = $this->getModules();

        foreach ($modules as $module) {

            $content = $this->modStub($module);
            $file = "{$module['capPlural']}Controller.php";

            $path = app_path('Http/Controllers') . '/' . $file;

            if (file_exists($path)) {
                continue;
            }

            file_put_contents($path, $content);
        }

        $this->generateRoutes($modules);

    }


    protected function modStub($module)
    {

        $stub = $this->getStub();

        $stub = str_replace('{{Module}}', $module['capName'], $stub);
        $stub = str_replace('{{module}}', $module['smName'], $stub);
        $stub = str_replace('{{Modules}}', $module['capPlural'], $stub);
        return str_replace('{{modules}}', $module['smPlural'], $stub);

    }


    protected function getModules(): array
    {
        /**
         * Modules
         * - attributes
         * - categories
         * - currencies
         * - payment_methods
         * - stores
         * - taxes
         * - tier_groups
         *
         * - accounts
         * - customers
         * - inventories
         * - orders
         * - products
         * - sellers
         */


        return [
            ['capName' => 'Attribute', 'smName' => 'attribute', 'capPlural' => 'Attributes', 'smPlural' => 'attributes'],
            [
                'capName' => 'Category',
                'smName' => 'category',
                'capPlural' => 'Categories',
                'smPlural' => 'categories'
            ],
            [
                'capName' => 'Currency',
                'smName' => 'currency',
                'capPlural' => 'Currencies',
                'smPlural' => 'currencies'
            ],
            [
                'capName' => 'PaymentMethod',
                'smName' => 'payment_method',
                'capPlural' => 'PaymentMethods',
                'smPlural' => 'payment_methods'
            ],
            ['capName' => 'Store', 'smName' => 'store', 'capPlural' => 'Stores', 'smPlural' => 'stores'],
            ['capName' => 'Tax', 'smName' => 'tax', 'capPlural' => 'Taxes', 'smPlural' => 'taxes'],
            [
                'capName' => 'TierGroup',
                'smName' => 'tier_group',
                'capPlural' => 'TierGroups',
                'smPlural' => 'tier_groups'
            ],
            ['capName' => 'Account', 'smName' => 'account', 'capPlural' => 'Accounts', 'smPlural' => 'accounts'],
            [
                'capName' => 'Customer',
                'smName' => 'customer',
                'capPlural' => 'Customers',
                'smPlural' => 'customers'
            ],
            [
                'capName' => 'Inventory',
                'smName' => 'inventory',
                'capPlural' => 'Inventories',
                'smPlural' => 'inventories'
            ],
            ['capName' => 'Order', 'smName' => 'order', 'capPlural' => 'Orders', 'smPlural' => 'orders'],
            ['capName' => 'Product', 'smName' => 'product', 'capPlural' => 'Products', 'smPlural' => 'products'],
            ['capName' => 'Seller', 'smName' => 'seller', 'capPlural' => 'Sellers', 'smPlural' => 'sellers'],
        ];

    }


    protected function getStub(): bool|string
    {
        $file = __DIR__ . '/ModuleController.php.stub';

        return file_get_contents($file);
    }

    private function generateRoutes(array $modules)
    {

        $routeFile = base_path('routes/api.php');

        $routes = '//--SaasBridge--//' . PHP_EOL;

        foreach ($modules as $module) {

            $routes .= <<<PHP
Route::module('{$module['smPlural']}',\App\Http\Controllers\\{$module['capPlural']}Controller::class);\n
PHP;

        }


        $content = file_get_contents($routeFile);

        $content = str_replace('//--SaasBridge--//', $routes, $content);

        //append
        file_put_contents($routeFile, $content);

    }
}
