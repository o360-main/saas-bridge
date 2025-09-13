# Very short description of the package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/o360-main/saas-bridge.svg?style=flat-square)](https://packagist.org/packages/o360-main/saas-bridge)
[![Total Downloads](https://img.shields.io/packagist/dt/o360-main/saas-bridge.svg?style=flat-square)](https://packagist.org/packages/o360-main/saas-bridge)
![GitHub Actions](https://github.com/o360-main/saas-bridge/actions/workflows/main.yml/badge.svg)

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what PSRs you support to avoid any confusion with users and contributors.

## Installation

You can install the package via composer:

```bash
composer require o360-main/saas-bridge
```

## Usage

### Features

This package provides the following features:

#### 🔒 Helpers\ConnectionSyncMutex - Redis-Based Locking
Enterprise-grade distributed locking mechanism similar to Go's `sync.Mutex`. Ensures thread-safe operations across distributed systems with automatic cleanup and timeout handling.

**Quick Example:**
```php
use O360Main\SaasBridge\Helpers\ConnectionSyncMutex;

// Simple synchronized execution
$result = ConnectionSyncMutex::make('resource_key')->synchronized(function() {
    // Only one process can execute this at a time
    return performCriticalOperation();
});
```

📖 **[Complete Documentation](docs/connection-sync-mutex.md)** - Configuration, examples, troubleshooting

#### 🔄 Helpers\JsonDataMapper - JSON & Array Processing
Enterprise-grade JSON-to-JSON and array-to-array transformation engine with configurable mapping rules, 15+ built-in transformers, and nested data support. Optimized for high-performance API integrations.

**Quick Examples:**
```php
use O360Main\SaasBridge\Helpers\JsonDataMapper;

// JSON to JSON transformation
$result = JsonDataMapper::quickTransform($sourceJson, [
    'name' => 'user.full_name',
    'email' => ['source' => 'user.email', 'transform' => 'lowercase']
]);

// PHP Array to Array transformation (no JSON parsing overhead)
$mapper = JsonDataMapper::create($rules);
$result = $mapper->transformArray($sourceArray);
```

📖 **[Complete Documentation](docs/json-data-mapper.md)** | **[Examples](docs/json-mapper-examples.md)** | **[Performance](docs/json-mapper-performance.md)** | **[Troubleshooting](docs/json-mapper-troubleshooting.md)**

#### 🌐 Helpers\HttpDataMapper - Laravel HTTP Integration
Advanced HTTP response transformation layer extending JsonMapper. Features fluent pipelines, batch processing, error handling, webhook support, and paginated API responses.

**Quick Examples:**
```php
use O360Main\SaasBridge\Helpers\HttpDataMapper;
use Illuminate\Support\Facades\Http;

// Transform HTTP response directly
$response = Http::get('https://api.example.com/users');
$mapper = HttpDataMapper::create($rules);
$users = $mapper->transformResponse($response);

// Fluent pipeline for complex transformations
$result = $mapper->pipe()
    ->from($response)
    ->transform()
    ->extract('data')
    ->filter(fn($item) => $item['active'])
    ->take(10)
    ->toArray();
```

📖 **[HTTP Documentation](docs/http-data-mapper.md)** - Response transformation, pipelines, webhooks, batch processing

#### ⚡ Helpers\RateLimitedHttpClient - Rate Limited HTTP Wrapper
Enterprise-grade HTTP client wrapper with Redis-based rate limiting, automatic blocking using ConnectionSyncMutex when limits are exhausted, and API-specific configurations. Perfect for external API integrations with rate limit requirements.

**Quick Examples:**
```php
use O360Main\SaasBridge\Helpers\RateLimitedHttpClient;

// Simple rate limited requests
$client = RateLimitedHttpClient::make();
$response = $client->get('https://api.example.com/users');

// API-specific rate limits
$shopifyClient = RateLimitedHttpClient::forApi('shopify');
$products = $shopifyClient->get('/admin/api/2023-01/products.json');

// Batch operations with shared rate limiting
$responses = $client->batch([
    'users' => ['method' => 'GET', 'url' => '/api/users'],
    'orders' => ['method' => 'GET', 'url' => '/api/orders'],
]);

// Custom rate limiting configuration
$client = RateLimitedHttpClient::make()->configure([
    'max_requests' => 30,
    'window_seconds' => 60,
    'block_when_exhausted' => false
]);
```

📖 **[Complete Documentation](docs/rate-limited-http-client.md)** - Configuration, API limits, monitoring, troubleshooting

---

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email vimal@o360integrator.com instead of using the issue tracker.

## Credits

-   [o360](https://github.com/o360-main)
-   [All Contributors](../../contributors)

## License

The The Unlicensed. Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
