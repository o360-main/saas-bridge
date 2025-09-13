# JsonDataMapper - Detailed Usage Guide

This comprehensive guide covers advanced usage patterns, real-world scenarios, performance optimization, and troubleshooting for the JsonDataMapper.

## Table of Contents

1. [Installation & Setup](#installation--setup)
2. [Basic Concepts](#basic-concepts)
3. [Real-World Scenarios](#real-world-scenarios)
4. [Advanced Patterns](#advanced-patterns)
5. [Performance Optimization](#performance-optimization)
6. [Best Practices](#best-practices)
7. [Troubleshooting](#troubleshooting)
8. [FAQ](#frequently-asked-questions)

## Installation & Setup

### Basic Setup

```php
use O360Main\SaasBridge\Helpers\JsonDataMapper;

// Initialize mapper
$mapper = JsonDataMapper::create();

// Or with initial rules
$mapper = JsonDataMapper::create([
    'target_field' => 'source_field'
]);
```

### Dependency Injection (Laravel)

```php
// In a Service Provider
$this->app->singleton('json.mapper', function ($app) {
    return JsonDataMapper::create()->addTransformer('custom', function($value) {
        return $app['some.service']->process($value);
    });
});

// In your controller
public function transform(Request $request)
{
    $mapper = app('json.mapper');
    return $mapper->transformJson($request->input('data'));
}
```

## Basic Concepts

### Mapping Types

#### 1. Simple Mapping
```php
// Direct field mapping
$mapper = JsonDataMapper::create([
    'new_name' => 'old_name',
    'email' => 'email_address'
]);
```

#### 2. Advanced Mapping with Transformers
```php
$mapper = JsonDataMapper::create([
    'name' => ['source' => 'full_name', 'transform' => 'uppercase'],
    'age' => ['source' => 'years_old', 'transform' => 'int', 'default' => 0]
]);
```

#### 3. Nested Path Mapping
```php
$mapper = JsonDataMapper::create([
    'user_name' => 'user.profile.name',
    'user_email' => 'user.contact.email',
    'first_order' => 'user.orders[0].id'
]);
```

### Data Flow

```
Source JSON → Parse → Apply Rules → Transform → Output JSON
     ↓           ↓         ↓           ↓         ↓
   Input    Extract   Map Fields   Apply     Result
   Data     Values    & Arrays   Transform   Data
```

## Real-World Scenarios

### Scenario 1: E-commerce API Integration

Transform Shopify product data to internal format:

```php
$shopifyProduct = '{
    "id": 123456789,
    "title": "Awesome Widget",
    "body_html": "<p>Great product</p>",
    "vendor": "ACME Corp",
    "product_type": "Electronics",
    "handle": "awesome-widget",
    "variants": [
        {
            "id": 987654321,
            "price": "29.99",
            "sku": "AWE-001",
            "inventory_quantity": 100,
            "option1": "Red",
            "weight": 1.5
        },
        {
            "id": 987654322,
            "price": "32.99",
            "sku": "AWE-002",
            "inventory_quantity": 50,
            "option1": "Blue",
            "weight": 1.5
        }
    ],
    "images": [
        {"src": "https://example.com/image1.jpg", "alt": "Front view"},
        {"src": "https://example.com/image2.jpg", "alt": "Back view"}
    ],
    "tags": "electronics,gadgets,popular",
    "created_at": "2023-01-15T10:30:00Z"
}';

$mapper = JsonDataMapper::create([
    'product_id' => ['source' => 'id', 'transform' => 'string'],
    'name' => 'title',
    'description' => ['source' => 'body_html', 'transform' => 'strip_tags'],
    'brand' => 'vendor',
    'category' => 'product_type',
    'slug' => 'handle',
    'tag_list' => ['source' => 'tags', 'transform' => 'split_comma'],
    'created_date' => ['source' => 'created_at', 'transform' => 'date_format'],
    'variant_count' => ['source' => 'variants', 'transform' => 'array_count'],
    'min_price' => ['source' => 'variants', 'transform' => 'min_price'],
    'max_price' => ['source' => 'variants', 'transform' => 'max_price']
])
->addArrayMapping('variants', 'variants', [
    'variant_id' => ['source' => 'id', 'transform' => 'string'],
    'price' => ['source' => 'price', 'transform' => 'float'],
    'sku' => 'sku',
    'stock' => ['source' => 'inventory_quantity', 'transform' => 'int'],
    'color' => 'option1',
    'weight_kg' => ['source' => 'weight', 'transform' => 'float']
])
->addArrayExtraction('image_urls', 'images', 'src')
->addTransformer('strip_tags', fn($html) => strip_tags($html))
->addTransformer('min_price', fn($variants) => min(array_column($variants, 'price')))
->addTransformer('max_price', fn($variants) => max(array_column($variants, 'price')))
->setDefaults([
    'status' => 'active',
    'currency' => 'USD',
    'processed_at' => date('c')
]);

$internalProduct = $mapper->transformJson($shopifyProduct);
```

### Scenario 2: User Data Migration

Migrate user data from old system to new format:

```php
$oldUserData = '{
    "user_id": "12345",
    "personal_info": {
        "fname": "John",
        "lname": "Doe",
        "email": "john.doe@example.com",
        "phone": "+1-555-0123",
        "birthdate": "1985-03-15"
    },
    "address_info": [
        {
            "type": "home",
            "street": "123 Main St",
            "city": "Springfield",
            "state": "IL",
            "zip": "62701",
            "country": "US",
            "is_primary": true
        },
        {
            "type": "work",
            "street": "456 Business Ave",
            "city": "Springfield",
            "state": "IL", 
            "zip": "62702",
            "country": "US",
            "is_primary": false
        }
    ],
    "preferences": {
        "newsletter": "yes",
        "sms_alerts": "no",
        "theme": "dark"
    },
    "order_history": [
        {"order_id": "ORD001", "total": 99.99, "date": "2023-01-10"},
        {"order_id": "ORD002", "total": 149.50, "date": "2023-02-15"}
    ]
}';

$mapper = JsonDataMapper::create([
    'id' => ['source' => 'user_id', 'transform' => 'int'],
    'email' => 'personal_info.email',
    'phone' => ['source' => 'personal_info.phone', 'transform' => 'normalize_phone'],
    'birth_date' => ['source' => 'personal_info.birthdate', 'transform' => 'date_format'],
    'full_name' => ['source' => 'personal_info', 'transform' => 'build_full_name'],
    'primary_address' => ['source' => 'address_info', 'transform' => 'extract_primary_address'],
    'total_orders' => ['source' => 'order_history', 'transform' => 'array_count'],
    'total_spent' => ['source' => 'order_history', 'transform' => 'sum_order_totals']
])
->addNestedMapping('profile', 'personal_info', [
    'first_name' => 'fname',
    'last_name' => 'lname',
    'display_name' => ['source' => '.', 'transform' => 'create_display_name']
])
->addNestedMapping('settings', 'preferences', [
    'email_newsletter' => ['source' => 'newsletter', 'transform' => 'yes_no_to_bool'],
    'sms_notifications' => ['source' => 'sms_alerts', 'transform' => 'yes_no_to_bool'],
    'ui_theme' => 'theme'
])
->addArrayMapping('addresses', 'address_info', [
    'type' => 'type',
    'address_line' => 'street',
    'city' => 'city',
    'state_code' => 'state',
    'postal_code' => 'zip',
    'country_code' => 'country',
    'is_default' => 'is_primary'
])
->addTransformer('normalize_phone', function($phone) {
    return preg_replace('/[^0-9+]/', '', $phone);
})
->addTransformer('build_full_name', function($info) {
    return trim(($info['fname'] ?? '') . ' ' . ($info['lname'] ?? ''));
})
->addTransformer('extract_primary_address', function($addresses) {
    foreach ($addresses as $addr) {
        if ($addr['is_primary'] ?? false) {
            return $addr['street'] . ', ' . $addr['city'] . ', ' . $addr['state'];
        }
    }
    return null;
})
->addTransformer('sum_order_totals', function($orders) {
    return array_sum(array_column($orders, 'total'));
})
->addTransformer('yes_no_to_bool', fn($val) => strtolower($val) === 'yes')
->addTransformer('create_display_name', function($info) {
    return ($info['fname'] ?? '') . ' ' . substr($info['lname'] ?? '', 0, 1) . '.';
});

$newUserData = $mapper->transformJson($oldUserData);
```

### Scenario 3: Webhook Data Processing

Process webhook data from multiple sources:

```php
class WebhookProcessor
{
    private array $mappers = [];

    public function __construct()
    {
        $this->setupMappers();
    }

    private function setupMappers()
    {
        // Stripe webhook mapper
        $this->mappers['stripe'] = JsonDataMapper::create([
            'event_id' => 'id',
            'event_type' => 'type',
            'created_at' => ['source' => 'created', 'transform' => 'timestamp_to_date'],
            'customer_id' => 'data.object.customer',
            'amount' => ['source' => 'data.object.amount', 'transform' => 'cents_to_dollars']
        ])
        ->addTransformer('timestamp_to_date', fn($ts) => date('c', $ts))
        ->addTransformer('cents_to_dollars', fn($cents) => $cents / 100);

        // PayPal webhook mapper  
        $this->mappers['paypal'] = JsonDataMapper::create([
            'event_id' => 'id',
            'event_type' => 'event_type',
            'created_at' => 'create_time',
            'customer_id' => 'resource.payer.payer_info.payer_id',
            'amount' => ['source' => 'resource.amount.total', 'transform' => 'float']
        ]);

        // Custom API webhook mapper
        $this->mappers['custom'] = JsonDataMapper::create([
            'event_id' => 'webhook_id',
            'event_type' => 'action',
            'created_at' => 'timestamp',
            'customer_id' => 'user.id',
            'amount' => 'transaction.value'
        ]);
    }

    public function process(string $source, string $webhookData): array
    {
        if (!isset($this->mappers[$source])) {
            throw new InvalidArgumentException("Unknown webhook source: $source");
        }

        return $this->mappers[$source]->transformArray(json_decode($webhookData, true));
    }
}

// Usage
$processor = new WebhookProcessor();
$standardized = $processor->process('stripe', $stripeWebhookJson);
```

### Scenario 4: Multi-Language Content Processing

Transform multilingual content data:

```php
$contentData = '{
    "article_id": 12345,
    "content": {
        "en": {
            "title": "Amazing Article",
            "body": "This is the English content...",
            "tags": ["tech", "innovation"]
        },
        "es": {
            "title": "Artículo Increíble", 
            "body": "Este es el contenido en español...",
            "tags": ["tecnología", "innovación"]
        },
        "fr": {
            "title": "Article Incroyable",
            "body": "Ceci est le contenu français...",
            "tags": ["technologie", "innovation"]
        }
    },
    "metadata": {
        "author": "John Doe",
        "published_at": "2023-01-15T10:00:00Z",
        "category": "Technology"
    }
}';

$mapper = JsonDataMapper::create([
    'id' => ['source' => 'article_id', 'transform' => 'int'],
    'author' => 'metadata.author',
    'category' => 'metadata.category',
    'published_date' => ['source' => 'metadata.published_at', 'transform' => 'date_iso'],
    'languages' => ['source' => 'content', 'transform' => 'extract_languages'],
    'default_language' => ['source' => 'content', 'transform' => 'detect_primary_language']
])
->addNestedMapping('translations', 'content', [
    'en' => ['source' => 'en', 'transform' => 'process_translation'],
    'es' => ['source' => 'es', 'transform' => 'process_translation'],
    'fr' => ['source' => 'fr', 'transform' => 'process_translation']
])
->addTransformer('extract_languages', fn($content) => array_keys($content))
->addTransformer('detect_primary_language', function($content) {
    // Assume primary language has longest content
    $lengths = array_map(fn($lang) => strlen($lang['body'] ?? ''), $content);
    return array_search(max($lengths), $lengths);
})
->addTransformer('process_translation', function($translation) {
    return [
        'title' => $translation['title'] ?? '',
        'body' => $translation['body'] ?? '',
        'tags' => $translation['tags'] ?? [],
        'word_count' => str_word_count($translation['body'] ?? ''),
        'char_count' => strlen($translation['body'] ?? '')
    ];
});

$processedContent = $mapper->transformJson($contentData);
```

## Advanced Patterns

### Pattern 1: Conditional Transformations

```php
$mapper = JsonDataMapper::create()
    ->addTransformer('conditional_status', function($data) {
        $status = $data['status'] ?? '';
        $stock = (int)($data['stock'] ?? 0);
        
        return match(true) {
            $status === 'discontinued' => 'unavailable',
            $stock <= 0 => 'out_of_stock', 
            $stock < 10 => 'low_stock',
            default => 'available'
        };
    });

$mapper->addMapping('availability', ['source' => '.', 'transform' => 'conditional_status']);
```

### Pattern 2: Data Aggregation

```php
$mapper = JsonDataMapper::create()
    ->addTransformer('calculate_metrics', function($orders) {
        if (empty($orders)) {
            return ['count' => 0, 'total' => 0, 'average' => 0];
        }
        
        $total = array_sum(array_column($orders, 'amount'));
        $count = count($orders);
        
        return [
            'count' => $count,
            'total' => round($total, 2),
            'average' => round($total / $count, 2),
            'min' => min(array_column($orders, 'amount')),
            'max' => max(array_column($orders, 'amount'))
        ];
    });

$mapper->addMapping('order_metrics', ['source' => 'orders', 'transform' => 'calculate_metrics']);
```

### Pattern 3: Dynamic Field Generation

```php
$mapper = JsonDataMapper::create()
    ->addTransformer('generate_variants', function($product) {
        $variants = [];
        $colors = $product['colors'] ?? [];
        $sizes = $product['sizes'] ?? [];
        
        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                $variants[] = [
                    'sku' => $product['base_sku'] . '-' . strtoupper($color) . '-' . strtoupper($size),
                    'color' => $color,
                    'size' => $size,
                    'price' => $product['base_price'] ?? 0
                ];
            }
        }
        
        return $variants;
    });

$mapper->addMapping('product_variants', ['source' => '.', 'transform' => 'generate_variants']);
```

### Pattern 4: Complex Validation

```php
$mapper = JsonDataMapper::create()
    ->addTransformer('validate_and_clean', function($email) {
        $email = trim(strtolower($email));
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email: $email");
        }
        
        return $email;
    })
    ->addTransformer('validate_phone', function($phone) {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        if (strlen($phone) < 10) {
            throw new InvalidArgumentException("Invalid phone: $phone");
        }
        
        return $phone;
    });
```

## Performance Optimization

### 1. Reuse Mapper Instances

```php
// Bad - Creates new mapper each time
foreach ($dataItems as $item) {
    $mapper = JsonDataMapper::create($rules);
    $result[] = $mapper->transformArray($item);
}

// Good - Reuse mapper instance
$mapper = JsonDataMapper::create($rules);
foreach ($dataItems as $item) {
    $result[] = $mapper->transformArray($item);
}
```

### 2. Use transformCollection for Bulk Operations

```php
// Transform multiple items efficiently
$mapper = JsonDataMapper::create($rules);
$results = $mapper->transformCollection($dataItems);
```

### 3. Optimize Complex Transformers

```php
// Cache expensive operations
$mapper->addTransformer('expensive_lookup', function($id) {
    static $cache = [];
    
    if (!isset($cache[$id])) {
        $cache[$id] = expensiveDatabaseLookup($id);
    }
    
    return $cache[$id];
});
```

### 4. Minimize Deep Nesting

```php
// Less efficient - Deep nesting
'value' => 'level1.level2.level3.level4.target'

// More efficient - Direct access when possible
'value' => 'target_field'
```

### 5. Use Built-in Transformers

```php
// Built-in transformers are optimized
'count' => ['source' => 'items', 'transform' => 'array_count']

// Custom transformers have overhead
'count' => ['source' => 'items', 'transform' => 'custom_counter']
```

## Best Practices

### 1. Error Handling

```php
try {
    $result = $mapper->transformJson($sourceJson);
} catch (InvalidArgumentException $e) {
    logger()->error('JSON transformation failed', [
        'error' => $e->getMessage(),
        'source' => $sourceJson
    ]);
    
    // Return default structure or re-throw
    return ['error' => 'transformation_failed'];
}
```

### 2. Configuration Management

```php
// Store complex mappings in config files
// config/mappings/product-transform.php
return [
    'mapping' => [
        'id' => ['source' => 'product_id', 'transform' => 'int'],
        'name' => 'title'
    ],
    'defaults' => [
        'status' => 'active'
    ],
    'transformers' => [
        'custom_transform' => function($value) {
            return strtoupper($value);
        }
    ]
];

// Load in application
$mapper = JsonDataMapper::fromConfig('config/mappings/product-transform.php');
```

### 3. Testing Transformations

```php
class JsonMapperTest extends TestCase
{
    public function test_product_transformation()
    {
        $sourceData = [
            'product_id' => 123,
            'title' => 'Test Product',
            'price' => '29.99'
        ];
        
        $expected = [
            'id' => 123,
            'name' => 'Test Product', 
            'price' => 29.99,
            'currency' => 'USD'
        ];
        
        $mapper = JsonDataMapper::create([
            'id' => ['source' => 'product_id', 'transform' => 'int'],
            'name' => 'title',
            'price' => ['source' => 'price', 'transform' => 'float']
        ])->setDefaults(['currency' => 'USD']);
        
        $result = $mapper->transformArray($sourceData);
        
        $this->assertEquals($expected, $result);
    }
}
```

### 4. Documentation

```php
class ProductTransformer
{
    /**
     * Transform Shopify product to internal format
     * 
     * Input: Shopify Product JSON
     * Output: Internal Product Structure
     * 
     * Transformations:
     * - id: string -> int
     * - variants: array transformation with price conversion
     * - tags: comma-separated string -> array
     */
    public static function getMapper(): JsonDataMapper
    {
        return JsonDataMapper::create([
            'id' => ['source' => 'id', 'transform' => 'int'],
            'name' => 'title',
            'tags' => ['source' => 'tags', 'transform' => 'split_comma']
        ]);
    }
}
```

## Troubleshooting

### Common Issues

#### 1. Missing Fields

**Problem:** Required field not found in source data

```php
// Enable strict mode to catch missing fields
$mapper = JsonDataMapper::create([], true);

// Or provide defaults
$mapper->setDefaults(['missing_field' => null]);
```

#### 2. Invalid JSON

**Problem:** Source JSON is malformed

```php
$sourceData = json_decode($json, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    throw new InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
}
```

#### 3. Type Conversion Errors

**Problem:** Transformer fails on unexpected data types

```php
$mapper->addTransformer('safe_int', function($value) {
    if ($value === null || $value === '') {
        return 0;
    }
    return (int) $value;
});
```

#### 4. Memory Issues with Large Arrays

**Problem:** Processing large datasets causes memory issues

```php
// Process in chunks
$chunks = array_chunk($largeArray, 1000);
$results = [];

foreach ($chunks as $chunk) {
    $results = array_merge($results, $mapper->transformCollection($chunk));
    
    // Free memory
    unset($chunk);
    if (memory_get_usage() > 100 * 1024 * 1024) { // 100MB
        gc_collect_cycles();
    }
}
```

#### 5. Circular References

**Problem:** Source data contains circular references

```php
$mapper->addTransformer('safe_json_encode', function($data) {
    return json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR);
});
```

### Debugging Tips

#### 1. Log Transformations

```php
$mapper->addTransformer('debug_log', function($value) {
    logger()->debug('Transform value', ['input' => $value]);
    return $value;
});
```

#### 2. Validate Output Structure

```php
$result = $mapper->transformJson($json);

// Validate required fields exist
$requiredFields = ['id', 'name', 'email'];
foreach ($requiredFields as $field) {
    if (!isset($result[$field])) {
        throw new InvalidArgumentException("Missing required field: $field");
    }
}
```

#### 3. Step-by-Step Debugging

```php
// Transform step by step for debugging
$data = json_decode($json, true);
echo "Source: " . json_encode($data) . "\n";

$result = $mapper->transformArray($data);
echo "Result: " . json_encode($result) . "\n";

// Check specific transformations
echo "Mapping rules: " . json_encode($mapper->getMappingRules()) . "\n";
echo "Defaults: " . json_encode($mapper->getDefaults()) . "\n";
```

## Frequently Asked Questions

### Q: Can I transform the same JSON with different rules?

A: Yes, you can create multiple mapper instances or reconfigure the same instance:

```php
$mapper1 = JsonDataMapper::create($rules1);
$mapper2 = JsonDataMapper::create($rules2);

$result1 = $mapper1->transformJson($json);
$result2 = $mapper2->transformJson($json);

// Or reconfigure
$mapper1->setMappingRules($rules2);
$result3 = $mapper1->transformJson($json);
```

### Q: How do I handle optional fields?

A: Use defaults or conditional transformations:

```php
$mapper = JsonDataMapper::create([
    'name' => 'title',
    'description' => ['source' => 'desc', 'default' => 'No description available']
])
->setDefaults([
    'status' => 'active',
    'created_at' => date('c')
]);
```

### Q: Can I transform non-JSON data?

A: Yes, you can work directly with PHP arrays:

```php
$data = [
    'name' => 'John',
    'age' => '25'
];

$result = $mapper->transformArray($data);
```

### Q: How do I handle very large JSON files?

A: Use streaming or chunk processing:

```php
// For very large files, consider streaming
$handle = fopen('large-file.json', 'r');
$data = json_decode(stream_get_contents($handle), true);
fclose($handle);

// Process in chunks
$results = $mapper->transformCollection($data);
```

### Q: Can I use mapper in queued jobs?

A: Yes, but be careful with closures in transformers:

```php
// This won't work in queued jobs (closures can't be serialized)
$mapper->addTransformer('closure', fn($val) => $val);

// This works (class methods can be serialized)
class CustomTransformer {
    public function transform($value) {
        return strtoupper($value);
    }
}

$mapper->addTransformer('custom', [new CustomTransformer(), 'transform']);
```

### Q: How do I handle different API versions?

A: Create version-specific mappers:

```php
class ApiTransformer {
    public static function getMapper($version) {
        return match($version) {
            'v1' => self::getV1Mapper(),
            'v2' => self::getV2Mapper(),
            default => throw new InvalidArgumentException("Unsupported version: $version")
        };
    }
    
    private static function getV1Mapper() {
        return JsonDataMapper::create([
            'id' => 'user_id',
            'name' => 'username'
        ]);
    }
    
    private static function getV2Mapper() {
        return JsonDataMapper::create([
            'id' => 'id',
            'name' => 'display_name'
        ]);
    }
}
```

---

This guide covers the most common usage patterns and scenarios. For specific use cases not covered here, refer to the main documentation or create custom transformers to meet your needs.