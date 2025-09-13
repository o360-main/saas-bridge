# JsonDataMapper - JSON & Array Transformation Helper

The `JsonDataMapper` class provides powerful transformation capabilities for JSON-to-JSON, array-to-array, and mixed format conversions with configurable mapping rules, built-in transformers, and support for nested data structures.

## 📚 Documentation

- 📖 **[Real-World Examples](json-mapper-examples.md)** - E-commerce, API integration, webhooks, user migration
- ⚡ **[Performance Guide](json-mapper-performance.md)** - Optimization, memory management, large datasets  
- 🔧 **[Troubleshooting](json-mapper-troubleshooting.md)** - Common issues, debugging, error handling
- 🌐 **[HTTP Data Mapper](http-data-mapper.md)** - Laravel HTTP response transformation
- 📋 **[API Reference](json-mapper-api-reference.md)** - Complete method and transformer reference
- 📚 **[Detailed Usage Guide](json-mapper-detailed-usage.md)** - Comprehensive reference (all-in-one)

## Transformation Modes

### JSON to JSON (String to String)

```php
use O360Main\SaasBridge\Helpers\JsonDataMapper;

// Source JSON string
$sourceJson = '{"user_name": "john", "user_email": "john@example.com", "age": 25}';

$mapper = JsonDataMapper::create([
    'name' => 'user_name',
    'email' => 'user_email',
    'years_old' => 'age'
]);

$resultJson = $mapper->transformJson($sourceJson);
// Returns JSON string: {"name": "john", "email": "john@example.com", "years_old": 25}
```

### Array to Array (PHP Arrays)

```php
// Source PHP array (no JSON parsing needed)
$sourceArray = [
    'user_name' => 'john',
    'user_email' => 'john@example.com',
    'age' => 25
];

$mapper = JsonDataMapper::create([
    'name' => 'user_name',
    'email' => 'user_email', 
    'years_old' => 'age'
]);

$resultArray = $mapper->transformArray($sourceArray);
// Returns PHP array: ['name' => 'john', 'email' => 'john@example.com', 'years_old' => 25]
```

### Mixed Format Conversions

```php
$mapper = JsonDataMapper::create($rules);

// JSON string → PHP array
$array = $mapper->transformJsonToArray($jsonString);

// PHP array → JSON string  
$json = $mapper->transformArrayToJson($phpArray);

// Quick JSON transformation (static method)
$result = JsonDataMapper::quickTransform($sourceJson, $mappingRules);
```

## Basic Usage

### Simple Field Mapping

### Quick Transform

```php
// One-liner for simple transformations
$result = JsonDataMapper::quickTransform($sourceJson, [
    'full_name' => 'user_name',
    'contact_email' => 'user_email'
]);
```

## Advanced Mapping

### Nested Field Access

```php
$sourceJson = '{
    "user": {
        "profile": {
            "first_name": "John",
            "last_name": "Doe"
        },
        "settings": {
            "timezone": "UTC"
        }
    }
}';

$mapper = JsonDataMapper::create([
    'name' => 'user.profile.first_name',
    'surname' => 'user.profile.last_name',
    'tz' => 'user.settings.timezone'
]);
```

### Nested Output Structure

```php
$mapper = JsonDataMapper::create([
    'user.name' => 'full_name',
    'user.contact.email' => 'email',
    'user.contact.phone' => 'phone'
]);

// Creates nested output structure
// Result: {"user": {"name": "...", "contact": {"email": "...", "phone": "..."}}}
```

## Transformers

### Built-in Transformers

```php
$mapper = JsonDataMapper::create([
    'name' => ['source' => 'user_name', 'transform' => 'uppercase'],
    'email' => ['source' => 'user_email', 'transform' => 'lowercase'],
    'age' => ['source' => 'age_string', 'transform' => 'int'],
    'created_at' => ['source' => 'timestamp', 'transform' => 'date_iso'],
    'tags' => ['source' => 'tag_string', 'transform' => 'split_comma']
]);
```

**Available Built-in Transformers:**
- `string`, `int`, `float`, `bool`, `array` - Type casting
- `uppercase`, `lowercase`, `trim` - String manipulation
- `date_iso`, `date_format`, `timestamp` - Date formatting
- `json_decode`, `json_encode` - JSON handling
- `null_if_empty` - Convert empty values to null
- `split_comma`, `join_comma` - Array/string conversion

### Custom Transformers

```php
$mapper = JsonDataMapper::create()
    ->addTransformer('money_format', function($value) {
        return number_format((float)$value, 2);
    })
    ->addTransformer('initials', function($name) {
        return strtoupper(substr($name, 0, 1));
    })
    ->setMappingRules([
        'price' => ['source' => 'amount', 'transform' => 'money_format'],
        'initial' => ['source' => 'first_name', 'transform' => 'initials']
    ]);
```

## Configuration Options

### Default Values

```php
$mapper = JsonDataMapper::create([
    'name' => 'user_name',
    'email' => 'user_email'
])
->setDefaults([
    'status' => 'active',
    'role' => 'user',
    'created_at' => date('Y-m-d H:i:s')
]);

// Missing fields will use default values
```

### Advanced Mapping with Defaults

```php
$mapper = JsonDataMapper::create([
    'name' => [
        'source' => 'user_name',
        'default' => 'Anonymous',
        'transform' => 'trim'
    ],
    'age' => [
        'source' => 'user_age',
        'default' => 0,
        'transform' => 'int'
    ]
]);
```

### Strict Mode

```php
$mapper = JsonDataMapper::create([], true) // Enable strict mode
    ->setMappingRules([
        'required_field' => 'source_field'
    ]);

// Throws exception if 'source_field' is missing
```

## Real-World Examples

### API Response Transformation

```php
// Transform external API response to internal format
$externalApiResponse = '{
    "data": {
        "customer_info": {
            "first_name": "John",
            "last_name": "Doe",
            "email_address": "john@example.com"
        },
        "order_details": {
            "order_id": "ORD123",
            "total_amount": "99.99",
            "currency_code": "USD"
        }
    }
}';

$mapper = JsonDataMapper::create([
    'customer.name' => 'data.customer_info.first_name',
    'customer.surname' => 'data.customer_info.last_name',
    'customer.email' => 'data.customer_info.email_address',
    'order.id' => 'data.order_details.order_id',
    'order.total' => ['source' => 'data.order_details.total_amount', 'transform' => 'float'],
    'order.currency' => 'data.order_details.currency_code'
])
->setDefaults([
    'order.status' => 'pending',
    'processed_at' => date('c')
]);

$internalFormat = $mapper->transformJson($externalApiResponse);
```

### E-commerce Product Mapping

```php
$productData = '{
    "id": "123",
    "title": "Amazing Product",
    "description": "This is an amazing product",
    "price": "29.99",
    "categories": "electronics,gadgets,tech",
    "in_stock": "1",
    "created": "1640995200"
}';

$mapper = JsonDataMapper::create([
    'product_id' => ['source' => 'id', 'transform' => 'int'],
    'name' => ['source' => 'title', 'transform' => 'trim'],
    'summary' => 'description',
    'price_amount' => ['source' => 'price', 'transform' => 'float'],
    'category_list' => ['source' => 'categories', 'transform' => 'split_comma'],
    'available' => ['source' => 'in_stock', 'transform' => 'bool'],
    'created_date' => ['source' => 'created', 'transform' => 'date_iso']
])
->setDefaults([
    'currency' => 'USD',
    'featured' => false
]);
```

## Array and Nested JSON Support

### Array Indexing

Access specific array elements using index notation:

```php
$sourceJson = '{
    "products": [
        {"name": "Product A", "price": 10.00},
        {"name": "Product B", "price": 20.00}
    ]
}';

$mapper = JsonDataMapper::create([
    'first_product' => 'products[0].name',   // Gets "Product A"
    'second_price' => 'products[1].price',   // Gets 20.00
    'all_products' => 'products[*]'          // Gets entire array
]);
```

### Nested Array Transformation

Transform arrays of objects with custom mapping rules:

```php
$sourceJson = '{
    "orders": [
        {"id": 1, "customer_name": "John", "total": "99.99"},
        {"id": 2, "customer_name": "Jane", "total": "149.50"}
    ]
}';

$mapper = JsonDataMapper::create()
    ->addArrayMapping('processed_orders', 'orders', [
        'order_id' => ['source' => 'id', 'transform' => 'int'],
        'customer' => 'customer_name',
        'amount' => ['source' => 'total', 'transform' => 'float']
    ]);

$result = $mapper->transformJson($sourceJson);
// Result: {"processed_orders": [{"order_id": 1, "customer": "John", "amount": 99.99}, ...]}
```

### Array Field Extraction

Extract specific fields from array items:

```php
$mapper = JsonDataMapper::create()
    ->addArrayExtraction('customer_names', 'orders', 'customer_name')
    ->addArrayExtraction('order_totals', 'orders', 'total', 'float');

// Result: {"customer_names": ["John", "Jane"], "order_totals": [99.99, 149.50]}
```

### Array Filtering

Filter array items based on conditions:

```php
$mapper = JsonDataMapper::create()
    ->addArrayFilter('high_value_orders', 'orders', function($order) {
        return (float)($order['total'] ?? 0) > 100;
    });

// Only keeps orders with total > 100
```

### Nested Object Mapping

Transform nested objects with separate mapping rules:

```php
$sourceJson = '{
    "user": {
        "profile": {"first_name": "John", "last_name": "Doe"},
        "settings": {"theme": "dark", "notifications": true}
    }
}';

$mapper = JsonDataMapper::create()
    ->addNestedMapping('user_info', 'user.profile', [
        'full_name' => ['source' => 'first_name', 'transform' => 'uppercase'],
        'surname' => 'last_name'
    ])
    ->addNestedMapping('preferences', 'user.settings', [
        'ui_theme' => 'theme',
        'email_notifications' => 'notifications'
    ]);
```

### Advanced Array Transformers

New built-in transformers for array operations:

```php
$mapper = JsonDataMapper::create([
    'first_item' => ['source' => 'items', 'transform' => 'array_first'],
    'last_item' => ['source' => 'items', 'transform' => 'array_last'],
    'item_count' => ['source' => 'items', 'transform' => 'array_count'],
    'unique_tags' => ['source' => 'tags', 'transform' => 'array_unique'],
    'flattened' => ['source' => 'nested_array', 'transform' => 'array_flatten'],
    'filtered' => ['source' => 'items', 'transform' => 'array_filter_empty'],
    'space_separated' => ['source' => 'keywords', 'transform' => 'implode_space'],
    'pipe_separated' => ['source' => 'categories', 'transform' => 'implode_pipe']
]);
```

**Available Array Transformers:**
- `array_first` - Get first array element
- `array_last` - Get last array element  
- `array_count` - Count array elements
- `array_values` - Get array values (reindex)
- `array_keys` - Get array keys
- `array_unique` - Remove duplicates
- `array_flatten` - Flatten multidimensional array
- `array_filter_empty` - Remove empty values
- `implode_space` - Join array with spaces
- `implode_pipe` - Join array with pipes

## Collection Transformation

```php
$productsJson = '[
    {"name": "Product A", "price": "10.00"},
    {"name": "Product B", "price": "20.00"}
]';

$products = json_decode($productsJson, true);

$mapper = JsonDataMapper::create([
    'product_name' => 'name',
    'product_price' => ['source' => 'price', 'transform' => 'float']
])
->setDefaults(['currency' => 'USD']);

$transformedProducts = $mapper->transformCollection($products);
```

### Complex Nested Example

```php
$complexJson = '{
    "store": {
        "info": {"name": "My Store", "country": "US"},
        "products": [
            {
                "id": 1,
                "details": {"name": "Widget", "price": 29.99},
                "categories": ["electronics", "gadgets"],
                "reviews": [
                    {"rating": 5, "comment": "Great!"},
                    {"rating": 4, "comment": "Good"}
                ]
            }
        ]
    }
}';

$mapper = JsonDataMapper::create([
    'store_name' => 'store.info.name',
    'location' => 'store.info.country'
])
->addArrayMapping('items', 'store.products', [
    'product_id' => ['source' => 'id', 'transform' => 'int'],
    'name' => 'details.name',
    'price' => ['source' => 'details.price', 'transform' => 'float'],
    'category_list' => ['source' => 'categories', 'transform' => 'join_comma'],
    'avg_rating' => ['source' => 'reviews', 'transform' => 'calculate_avg_rating']
])
->addTransformer('calculate_avg_rating', function($reviews) {
    if (empty($reviews)) return 0;
    $total = array_sum(array_column($reviews, 'rating'));
    return round($total / count($reviews), 2);
});
```

## Configuration Files

### Create Mapping Configuration

```php
// config/product-mapping.php
return [
    'strict_mode' => false,
    'mapping' => [
        'id' => ['source' => 'product_id', 'transform' => 'int'],
        'name' => ['source' => 'title', 'transform' => 'trim'],
        'price' => ['source' => 'amount', 'transform' => 'float'],
        'categories' => ['source' => 'cats', 'transform' => 'split_comma']
    ],
    'defaults' => [
        'status' => 'active',
        'currency' => 'USD'
    ],
    'transformers' => [
        'price_format' => function($value) {
            return number_format((float)$value, 2);
        }
    ]
];
```

### Load from Configuration

```php
$mapper = JsonDataMapper::fromConfig('config/product-mapping.php');
$result = $mapper->transformJson($sourceJson);
```

## Method Chaining

```php
$result = JsonDataMapper::create()
    ->addMapping('name', 'user_name')
    ->addMapping('email', ['source' => 'user_email', 'transform' => 'lowercase'])
    ->addDefault('status', 'active')
    ->addTransformer('custom', fn($val) => strtoupper($val))
    ->strictMode(true)
    ->transformJson($sourceJson);
```

## Error Handling

```php
try {
    $mapper = JsonDataMapper::create([], true); // Strict mode
    $result = $mapper->transformJson($invalidJson);
} catch (InvalidArgumentException $e) {
    // Handle missing required fields or invalid JSON
    logger()->error('JSON transformation failed: ' . $e->getMessage());
}
```

## Use Cases

### 1. API Integration
Transform external API responses to match your internal data structure.

### 2. Data Migration
Convert data from old format to new format during migrations.

### 3. Import/Export
Transform data between different file formats or systems.

### 4. Frontend Data Preparation
Format backend data for frontend consumption.

### 5. Webhook Processing
Standardize incoming webhook data from various sources.

## Performance Tips

1. **Reuse mapper instances** for multiple transformations with the same rules
2. **Use simple string mappings** when possible (faster than array configs)
3. **Avoid deep nesting** in source data paths when performance is critical
4. **Register transformers once** and reuse the mapper instance
5. **Use collections transformation** for bulk data processing

## Advanced Features

### Conditional Mapping

```php
$mapper->addTransformer('conditional_status', function($value) {
    return match($value) {
        '1', 'true', 'active' => 'enabled',
        '0', 'false', 'inactive' => 'disabled',
        default => 'unknown'
    };
});
```

### Complex Transformations

```php
$mapper->addTransformer('full_name', function($data) {
    return trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
});

// Use with source array
$mapping = [
    'name' => ['source' => '.', 'transform' => 'full_name'] // '.' means entire data
];
```