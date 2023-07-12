
# Always update package

```bash
composer update o360-main/saas-bridge
```


# Routes

Modules have the following routes compulsory

```php

    //Routes : /api/{module}/config
    public function config(Request $request = null);

    //Routes : /api/{module}/data
    public function data(Request $request = null);

    //Routes : /api/{module}/import
    public function import(Request $request = null);

    //Routes : /api/{module}/export
    public function export(Request $request = null);

    public function trigger(Request $request = null);

```


### Helper
Use following helper to generate routes for module
Just like Route::resource() helper
It will generate all above routes for you.

```php
 Route::module('categories', CategoriesController::class);
 Route::module('products', \App\Http\Controllers\ProductsController::class);
```

# Saas Bridge
- Saas Bridge is helper package to provide common functionality to all plugins.
- This is only works in Laravel. But We can use main saas without it. So keep in mind this is only helper. Not directly related to Main Code.
- This is only works in controller functions of modules. Which are used Plugin Middleware.
- Following Api you can use in your plugin everywhere

```php

 * @method static \Illuminate\Http\Client\PendingRequest api($version = null)
 * @method static array credentials()
 * @method static array config()
 
 //Use this as
 
    SaasBridge::api('v1')->get(credentials');
    SaasBridge::api('v1')->post('products', $data);

    //See Main ->Dashboard -> Credentials    
    SaasBridge::credentials(); // this contain all credentials saved from Dashboard by client
    //See Main ->Dashboard -> Configuration    
    SaasBridge::config(); // This contain module configs saved from Dashboard by client



```




# Config

- This is for config form generator.
- Please refer to example plugin for now.
- This allows to generate form in dashboard. So client can update the config from dashboard.
- This config we have to use on every api call depends on config.
- Configs are basically input we require to make decision how to manipulate data.
- See Dashboard -> Configuration -> {Module Name} -> Configuration section

# Data

- This is for data required statically. There is only one use case for now.
- For now, we only required in products data. see example plugin.
- There is no use case for other modules . But it is required to be there. for now, you can return empty array.
- There might be use case in the future. So we have to keep it there.


> Import and Export called on Particular Event

# Import [Event/Webhook]
- This is for import data from third party.
- When event/webhook is triggered, This function called by Main-Saas.
- When it called. You have to save data in CoreDB
- Use config to get credentials for third party.

# Export [Event/Webhook]
- This is for export data to third party.
- When new data added in CoreDB. This function called by Main-Saas.
- When it called. You have to send data to third party.
- Use config to get credentials for third party.

# Trigger [Actions]
- This is for trigger action from main saas
- When we have some action required manually call. for example products.import_all. to all product impoer
- Study example plugin. For response you have to follow set of format. See example plugin.
- Use it as route for actions. Make your code separate and Object-Oriented. See example plugin.
