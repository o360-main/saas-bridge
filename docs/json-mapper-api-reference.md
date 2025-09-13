# JsonDataMapper - API Reference

Quick reference for all JsonDataMapper methods and transformers.

## Class Methods

### Constructor & Factory Methods

```php
// Constructor
new JsonDataMapper(array $mappingRules = [], bool $strictMode = false)

// Factory method
JsonDataMapper::create(array $mappingRules = []): self
JsonDataMapper::fromConfig(string $configPath): self
JsonDataMapper::quickTransform(string $json, array $mappingRules): string
```

### Configuration Methods

```php
// Mapping rules
setMappingRules(array $rules): self
addMapping(string $targetField, string|array $sourceConfig): self
getMappingRules(): array

// Default values
setDefaults(array $defaults): self
addDefault(string $field, mixed $value): self
getDefaults(): array

// Transformers
addTransformer(string $name, callable $transformer): self
getTransformers(): array

// Array mappings
addArrayMapping(string $targetField, string $sourceField, array $itemMapping): self
addArrayExtraction(string $targetField, string $sourceField, string $extractField, ?string $transform = null): self
addArrayFilter(string $targetField, string $sourceField, callable $filter): self

// Nested mappings
addNestedMapping(string $targetField, string $sourceField, array $nestedMapping): self

// Mode settings
strictMode(bool $enabled = true): self
```

### Transformation Methods

```php
// Main transformation methods
transformJson(string $sourceJson): string                    // JSON string → JSON string
transformJsonToArray(string $sourceJson): array              // JSON string → PHP array
transformArrayToJson(array $sourceData): string              // PHP array → JSON string
transformArray(array $sourceData): array                     // PHP array → PHP array
transformCollection(array $sourceCollection): array          // Transform array of items
```

### Utility Methods

```php
getLockInfo(): array
```

## Built-in Transformers

### Type Conversions
```php
'string'     // Convert to string
'int'        // Convert to integer  
'integer'    // Alias for int
'float'      // Convert to float
'double'     // Alias for float
'bool'       // Convert to boolean
'boolean'    // Alias for bool
'array'      // Convert to array
```

### String Operations
```php
'uppercase'  // Convert to uppercase
'lowercase'  // Convert to lowercase
'trim'       // Trim whitespace
```

### Date/Time Operations
```php
'date_iso'       // Convert to ISO 8601 format (2023-01-15T10:30:00+00:00)
'date_format'    // Convert to Y-m-d format (2023-01-15)
'timestamp'      // Convert to Unix timestamp
```

### JSON Operations
```php
'json_decode'    // Decode JSON string to array
'json_encode'    // Encode array to JSON string
```

### Array Operations
```php
'array_first'        // Get first array element
'array_last'         // Get last array element
'array_count'        // Count array elements
'array_values'       // Get array values (reindex)
'array_keys'         // Get array keys
'array_unique'       // Remove duplicates
'array_flatten'      // Flatten multidimensional array
'array_filter_empty' // Remove empty values
```

### String/Array Conversions
```php
'split_comma'    // Split string by comma to array
'join_comma'     // Join array with commas
'implode_space'  // Join array with spaces
'implode_pipe'   // Join array with pipes
```

### Utility Transformers
```php
'null_if_empty'  // Convert empty values to null
```

## Mapping Rule Formats

### Simple Mapping
```php
'target_field' => 'source_field'
```

### Advanced Mapping
```php
'target_field' => [
    'source' => 'source_field',
    'transform' => 'transformer_name',
    'default' => 'default_value'
]
```

### Nested Path Access
```php
'target_field' => 'level1.level2.field'
'target_field' => 'items[0].name'        // First item
'target_field' => 'items[*]'             // All items
```

### Array Mapping Configuration
```php
// Array of objects transformation
$mapper->addArrayMapping('target_array', 'source_array', [
    'new_field' => 'old_field',
    'converted' => ['source' => 'value', 'transform' => 'int']
]);

// Extract specific field from array items
$mapper->addArrayExtraction('names', 'users', 'full_name');

// Filter array items
$mapper->addArrayFilter('adults', 'people', fn($person) => $person['age'] >= 18);
```

### Nested Object Mapping
```php
$mapper->addNestedMapping('user_profile', 'user', [
    'display_name' => 'name',
    'contact_email' => 'email'
]);
```

## Configuration File Format

```php
// config/mapping.php
return [
    'strict_mode' => false,
    'mapping' => [
        'id' => ['source' => 'user_id', 'transform' => 'int'],
        'name' => 'full_name',
        'email' => ['source' => 'email_address', 'transform' => 'lowercase']
    ],
    'defaults' => [
        'status' => 'active',
        'created_at' => date('c')
    ],
    'transformers' => [
        'custom_transformer' => function($value) {
            return strtoupper($value);
        }
    ]
];

// Load configuration
$mapper = JsonDataMapper::fromConfig('config/mapping.php');
```

## Error Handling

### Exceptions Thrown

```php
InvalidArgumentException    // Invalid JSON, missing required fields, configuration errors
RuntimeException           // Lock acquisition failures (when applicable)
TypeError                  // Type conversion errors in transformers
JsonException              // JSON parsing errors
```

### Error Handling Patterns

```php
try {
    $result = $mapper->transformJson($json);
} catch (InvalidArgumentException $e) {
    // Handle validation/transformation errors
    logger()->error('Transform failed: ' . $e->getMessage());
    return ['error' => 'transformation_failed'];
} catch (JsonException $e) {
    // Handle JSON parsing errors
    logger()->error('Invalid JSON: ' . $e->getMessage());
    return ['error' => 'invalid_json'];
}
```

## Performance Tips

1. **Reuse mapper instances** for multiple transformations
2. **Use built-in transformers** when possible (faster than custom ones)
3. **Process collections** with `transformCollection()` method
4. **Cache expensive operations** in custom transformers
5. **Minimize deep nesting** in source paths
6. **Use chunking** for large datasets to manage memory

## Common Patterns

### Conditional Transformation
```php
$mapper->addTransformer('conditional_status', function($data) {
    return match($data['type']) {
        'premium' => 'vip',
        'regular' => 'standard',
        default => 'basic'
    };
});
```

### Data Aggregation
```php
$mapper->addTransformer('calculate_total', function($items) {
    return array_sum(array_column($items, 'amount'));
});
```

### Safe Type Conversion
```php
$mapper->addTransformer('safe_int', function($value) {
    return is_numeric($value) ? (int)$value : 0;
});
```

### Validation
```php
$mapper->addTransformer('validate_email', function($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException("Invalid email: $email");
    }
    return $email;
});
```