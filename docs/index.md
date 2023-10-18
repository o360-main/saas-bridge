
# SaasBridge

- SaasBridge package is made for help in plugin development.
- It is laravel only package and contains all laravel specific code.
- SaasBridge provides code that avoids repeating code, and provides common functionality to all plugins.
- You have to use a Strict design pattern to use this package.

## Laravel/Php Features Used
- Laravel Http Client
- Laravel Request and Response
  - https://laravel.com/docs/10.x/validation#form-request-validation
  - Responsable Interface for create custom response classes
- php Interfaces to follow a strict design pattern
- Laravel Collection -> this is used to manipulate arrays


## Installation

- this will be pre-installed in starter template

```bash
composer require o360-main/saas-bridge
```
- Keep updating this package to get latest features

```bash
composer update o360-main/saas-bridge
```


## Commands
- SaasBridge package provides the following commands. Run `php artisan` to see all commands
- example `php artisan saas:{{command}}`
- `php artisan saas:generate:controller` : This will generate Stub/Template for all the modules, You can start work by changing file name. `php.stub to php`
- `php artisan saas:code-test` : This will run all the tests in the package. You have must resolve shown by this command.



## Routes

- Every plugin must have the following routes
- Modules may be added more in future

```php

Route::middleware(PluginSecretValidationMiddleware::class)->group(function () {


    //This two routes are only requiring plugin secret authentication
    Route::get('/manifest.json', [PluginController::class, 'manifest'])->name('manifest');
    Route::get('/ping', [PluginController::class, 'ping'])->name('ping');

    //Use plugin middleware for an initiate client. @see PluginMiddleware
    // SaasTokenValidationMiddleware is used to validate token from client
    // SaasBridge package handle most of the things. You can use it in your plugin
    Route::middleware([SaasTokenValidationMiddleware::class, PluginMiddleware::class])->group(function () {
        Route::post('/test-credentials', [PluginController::class, 'testCredentials']);

        Route::module('categories', Simple\CategoriesController::class);
        Route::module('stores', Simple\StoresController::class);
        Route::module('attributes', Simple\AttributesController::class);
        Route::module('taxes', Simple\TaxesController::class);
        Route::module('currencies', Simple\CurrenciesController::class);
        Route::module('payment_methods', Simple\PaymentMethodsController::class);
        Route::module('tier_groups', Simple\TierGroupsController::class);

        Route::module('customers', Complex\CustomersController::class);
        Route::module('products', Complex\ProductsController::class);
        Route::module('inventories', Complex\InventoriesController::class);
        Route::module('orders', Complex\OrdersController::class);

    });
});


```
