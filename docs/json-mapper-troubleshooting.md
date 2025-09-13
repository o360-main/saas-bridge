# JsonDataMapper - Troubleshooting Guide

This guide helps you diagnose and fix common issues when working with JsonDataMapper.

## Common Issues & Solutions

### 1. Missing Fields

**Problem:** Required field not found in source data

```
InvalidArgumentException: Required field 'email' not found in source data
```

**Solutions:**

#### Solution A: Use Default Values
```php
$mapper = JsonDataMapper::create([
    'email' => ['source' => 'email_address', 'default' => 'no-email@example.com']
])
->setDefaults([
    'status' => 'active',
    'created_at' => date('c')
]);
```

#### Solution B: Disable Strict Mode
```php
// Disable strict mode (default behavior)
$mapper = JsonDataMapper::create($rules, false);

// Or check if field exists before mapping
$mapper = JsonDataMapper::create([
    'email' => ['source' => 'email_address', 'default' => null]
]);
```

#### Solution C: Conditional Mapping
```php
$mapper->addTransformer('safe_email', function($data) {
    return $data['email_address'] ?? $data['email'] ?? $data['user_email'] ?? null;
});

$mapping = ['email' => ['source' => '.', 'transform' => 'safe_email']];
```

### 2. Invalid JSON Input

**Problem:** Source JSON is malformed

```
InvalidArgumentException: Invalid JSON input: Syntax error
```

**Solutions:**

#### Solution A: Validate JSON Before Processing
```php
function validateAndTransform(string $json, JsonDataMapper $mapper): array
{
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
    }
    
    return $mapper->transformArray($data);
}

// Usage with error handling
try {
    $result = validateAndTransform($jsonString, $mapper);
} catch (InvalidArgumentException $e) {
    logger()->error('JSON transformation failed', [
        'error' => $e->getMessage(),
        'json' => substr($jsonString, 0, 500) // Log first 500 chars
    ]);
    
    return ['error' => 'invalid_json'];
}
```

#### Solution B: Clean JSON Before Processing
```php
function cleanJson(string $json): string
{
    // Remove BOM if present
    $json = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $json);
    
    // Fix common JSON issues
    $json = str_replace(["\r\n", "\r", "\n"], "", $json); // Remove newlines
    $json = preg_replace('/,\s*}/', '}', $json); // Remove trailing commas
    $json = preg_replace('/,\s*]/', ']', $json);
    
    return trim($json);
}

$cleanedJson = cleanJson($rawJson);
$result = $mapper->transformJson($cleanedJson);
```

### 3. Type Conversion Errors

**Problem:** Transformer fails on unexpected data types

```
TypeError: Argument #1 ($value) must be of type string, array given
```

**Solutions:**

#### Solution A: Safe Type Converters
```php
$mapper->addTransformer('safe_int', function($value) {
    if ($value === null || $value === '') return 0;
    if (is_array($value)) return count($value);
    if (is_bool($value)) return $value ? 1 : 0;
    return (int) $value;
});

$mapper->addTransformer('safe_string', function($value) {
    if ($value === null) return '';
    if (is_array($value)) return json_encode($value);
    if (is_bool($value)) return $value ? 'true' : 'false';
    return (string) $value;
});

$mapper->addTransformer('safe_float', function($value) {
    if ($value === null || $value === '') return 0.0;
    if (is_array($value)) return (float) count($value);
    if (is_bool($value)) return $value ? 1.0 : 0.0;
    return (float) $value;
});
```

#### Solution B: Type Validation
```php
$mapper->addTransformer('validate_email', function($value) {
    if (!is_string($value)) {
        throw new InvalidArgumentException("Email must be a string, " . gettype($value) . " given");
    }
    
    $email = trim(strtolower($value));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException("Invalid email format: $email");
    }
    
    return $email;
});
```

### 4. Memory Issues with Large Data

**Problem:** Out of memory errors with large datasets

```
Fatal error: Allowed memory size of 134217728 bytes exhausted
```

**Solutions:**

#### Solution A: Process in Chunks
```php
class ChunkedProcessor
{
    private JsonDataMapper $mapper;
    private int $chunkSize;

    public function __construct(JsonDataMapper $mapper, int $chunkSize = 1000)
    {
        $this->mapper = $mapper;
        $this->chunkSize = $chunkSize;
    }

    public function processLargeDataset(array $data): \Generator
    {
        $chunks = array_chunk($data, $this->chunkSize);
        
        foreach ($chunks as $chunk) {
            $results = $this->mapper->transformCollection($chunk);
            
            foreach ($results as $result) {
                yield $result;
            }
            
            // Free memory
            unset($chunk, $results);
            
            // Force garbage collection if memory usage is high
            if (memory_get_usage() > 100 * 1024 * 1024) { // 100MB
                gc_collect_cycles();
            }
        }
    }
}

// Usage
$processor = new ChunkedProcessor($mapper, 500);

$outputFile = fopen('output.json', 'w');
foreach ($processor->processLargeDataset($hugeArray) as $item) {
    fwrite($outputFile, json_encode($item) . "\n");
}
fclose($outputFile);
```

#### Solution B: Streaming Processing
```php
function processJsonLines(string $inputFile, string $outputFile, JsonDataMapper $mapper): void
{
    $input = fopen($inputFile, 'r');
    $output = fopen($outputFile, 'w');
    
    if (!$input || !$output) {
        throw new RuntimeException('Failed to open files');
    }
    
    $lineNumber = 0;
    while (($line = fgets($input)) !== false) {
        $lineNumber++;
        
        try {
            $data = json_decode(trim($line), true);
            if ($data) {
                $transformed = $mapper->transformArray($data);
                fwrite($output, json_encode($transformed) . "\n");
            }
        } catch (Exception $e) {
            error_log("Error processing line $lineNumber: " . $e->getMessage());
        }
        
        // Periodic garbage collection
        if ($lineNumber % 1000 === 0) {
            gc_collect_cycles();
        }
    }
    
    fclose($input);
    fclose($output);
}
```

### 5. Circular Reference Issues

**Problem:** Source data contains circular references

```
Exception: Converting circular structure to JSON
```

**Solutions:**

#### Solution A: Safe JSON Encoding
```php
$mapper->addTransformer('safe_json_encode', function($data) {
    return json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE);
});

$mapper->addTransformer('detect_circular', function($data) {
    try {
        json_encode($data, JSON_THROW_ON_ERROR);
        return $data;
    } catch (JsonException $e) {
        // Handle circular reference by converting to string representation
        return '[Circular Reference Detected]';
    }
});
```

#### Solution B: Deep Clone with Circular Detection
```php
class CircularReferenceHandler
{
    private array $seen = [];

    public function cleanData($data, int $maxDepth = 10, int $currentDepth = 0)
    {
        if ($currentDepth > $maxDepth) {
            return '[Max Depth Reached]';
        }

        if (is_object($data)) {
            $objectId = spl_object_id($data);
            if (in_array($objectId, $this->seen)) {
                return '[Circular Reference]';
            }
            $this->seen[] = $objectId;
            $data = (array) $data;
        }

        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $value) {
                $cleaned[$key] = $this->cleanData($value, $maxDepth, $currentDepth + 1);
            }
            return $cleaned;
        }

        return $data;
    }
}

// Usage
$handler = new CircularReferenceHandler();
$mapper->addTransformer('clean_circular', function($data) use ($handler) {
    return $handler->cleanData($data);
});
```

### 6. Performance Issues

**Problem:** Transformations are too slow

**Diagnosis:**

```php
// Add timing to identify bottlenecks
$mapper->addTransformer('debug_timer', function($value) {
    $start = microtime(true);
    
    // Your transformation logic here
    $result = expensiveOperation($value);
    
    $duration = microtime(true) - $start;
    if ($duration > 0.1) { // Log operations taking more than 100ms
        error_log("Slow transformation: {$duration}s");
    }
    
    return $result;
});
```

**Solutions:**

#### Solution A: Cache Expensive Operations
```php
class CachedTransformer
{
    private array $cache = [];
    private int $maxCacheSize = 1000;

    public function cached(string $key, callable $transformer): callable
    {
        return function($value) use ($key, $transformer) {
            $cacheKey = $key . ':' . serialize($value);
            
            if (isset($this->cache[$cacheKey])) {
                return $this->cache[$cacheKey];
            }
            
            $result = $transformer($value);
            
            // Prevent memory bloat
            if (count($this->cache) >= $this->maxCacheSize) {
                $this->cache = array_slice($this->cache, -500, null, true);
            }
            
            $this->cache[$cacheKey] = $result;
            return $result;
        };
    }
}

// Usage
$cacher = new CachedTransformer();
$mapper->addTransformer('expensive_lookup', $cacher->cached('lookup', function($id) {
    return expensiveDatabaseLookup($id);
}));
```

#### Solution B: Batch Database Operations
```php
class BatchLookupOptimizer
{
    private array $lookupQueries = [];
    private array $lookupResults = [];

    public function addLookup(string $table, string $keyField, string $valueField): void
    {
        $this->lookupQueries[] = compact('table', 'keyField', 'valueField');
    }

    public function preloadLookups(array $data): void
    {
        foreach ($this->lookupQueries as $query) {
            $keys = $this->extractKeys($data, $query['keyField']);
            if (!empty($keys)) {
                $results = DB::table($query['table'])
                    ->whereIn($query['keyField'], array_unique($keys))
                    ->pluck($query['valueField'], $query['keyField'])
                    ->toArray();
                
                $this->lookupResults[$query['table']] = $results;
            }
        }
    }

    public function getLookupTransformer(string $table): callable
    {
        return function($key) use ($table) {
            return $this->lookupResults[$table][$key] ?? null;
        };
    }
}
```

### 7. Nested Array Access Issues

**Problem:** Cannot access deeply nested array elements

```
Trying to access array offset on value of type null
```

**Solutions:**

#### Solution A: Safe Nested Access
```php
$mapper->addTransformer('safe_get', function($data) {
    return function(string $path) use ($data) {
        $keys = explode('.', $path);
        $current = $data;
        
        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return null;
            }
            $current = $current[$key];
        }
        
        return $current;
    };
});

// Usage
$mapping = [
    'value' => ['source' => '.', 'transform' => 'safe_get', 'path' => 'level1.level2.value']
];
```

#### Solution B: Array Path Helper
```php
class ArrayPathHelper
{
    public static function get(array $array, string $path, $default = null)
    {
        $keys = explode('.', $path);
        $current = $array;
        
        foreach ($keys as $key) {
            // Handle array indices like "items[0]"
            if (preg_match('/^(\w+)\[(\d+)\]$/', $key, $matches)) {
                $arrayKey = $matches[1];
                $index = (int) $matches[2];
                
                if (!isset($current[$arrayKey]) || !is_array($current[$arrayKey])) {
                    return $default;
                }
                
                if (!isset($current[$arrayKey][$index])) {
                    return $default;
                }
                
                $current = $current[$arrayKey][$index];
            } else {
                if (!is_array($current) || !array_key_exists($key, $current)) {
                    return $default;
                }
                $current = $current[$key];
            }
        }
        
        return $current;
    }
}

// Usage in transformer
$mapper->addTransformer('array_get', function($path) {
    return function($data) use ($path) {
        return ArrayPathHelper::get($data, $path);
    };
});
```

## Debugging Techniques

### 1. Enable Debug Logging

```php
class DebugJsonMapper extends JsonDataMapper
{
    private bool $debugMode = false;

    public function enableDebug(bool $enabled = true): self
    {
        $this->debugMode = $enabled;
        return $this;
    }

    public function transformArray(array $sourceData): array
    {
        if ($this->debugMode) {
            echo "=== DEBUG: Input Data ===\n";
            echo json_encode($sourceData, JSON_PRETTY_PRINT) . "\n";
        }

        $result = parent::transformArray($sourceData);

        if ($this->debugMode) {
            echo "=== DEBUG: Output Data ===\n";
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            echo "=== DEBUG: Mapping Rules ===\n";
            echo json_encode($this->getMappingRules(), JSON_PRETTY_PRINT) . "\n";
        }

        return $result;
    }
}

// Usage
$mapper = new DebugJsonMapper($rules);
$mapper->enableDebug(true);
$result = $mapper->transformArray($data);
```

### 2. Step-by-Step Transformation Debugging

```php
class StepByStepDebugger
{
    public function debugTransformation(JsonDataMapper $mapper, array $data): array
    {
        echo "=== STEP 1: Source Data ===\n";
        $this->prettyPrint($data);

        echo "\n=== STEP 2: Mapping Rules ===\n";
        $this->prettyPrint($mapper->getMappingRules());

        echo "\n=== STEP 3: Default Values ===\n";
        $this->prettyPrint($mapper->getDefaults());

        echo "\n=== STEP 4: Available Transformers ===\n";
        $this->prettyPrint($mapper->getTransformers());

        echo "\n=== STEP 5: Transformation Result ===\n";
        $result = $mapper->transformArray($data);
        $this->prettyPrint($result);

        return $result;
    }

    private function prettyPrint($data): void
    {
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
}

// Usage
$debugger = new StepByStepDebugger();
$result = $debugger->debugTransformation($mapper, $sourceData);
```

### 3. Transformer Performance Profiling

```php
class TransformerProfiler
{
    private array $profiles = [];

    public function profileTransformer(string $name, callable $transformer): callable
    {
        return function($value) use ($name, $transformer) {
            $start = microtime(true);
            $startMemory = memory_get_usage();

            try {
                $result = $transformer($value);
                $success = true;
                $error = null;
            } catch (Exception $e) {
                $result = null;
                $success = false;
                $error = $e->getMessage();
            }

            $duration = microtime(true) - $start;
            $memoryUsed = memory_get_usage() - $startMemory;

            $this->profiles[$name][] = [
                'duration' => $duration,
                'memory' => $memoryUsed,
                'success' => $success,
                'error' => $error,
                'input_type' => gettype($value),
                'input_size' => is_string($value) ? strlen($value) : null
            ];

            if (!$success) {
                throw new Exception($error);
            }

            return $result;
        };
    }

    public function getReport(): array
    {
        $report = [];
        
        foreach ($this->profiles as $name => $executions) {
            $successCount = count(array_filter($executions, fn($e) => $e['success']));
            $totalExecutions = count($executions);
            $avgDuration = array_sum(array_column($executions, 'duration')) / $totalExecutions;
            $maxDuration = max(array_column($executions, 'duration'));
            $avgMemory = array_sum(array_column($executions, 'memory')) / $totalExecutions;

            $report[$name] = [
                'executions' => $totalExecutions,
                'success_rate' => ($successCount / $totalExecutions) * 100,
                'avg_duration_ms' => $avgDuration * 1000,
                'max_duration_ms' => $maxDuration * 1000,
                'avg_memory_kb' => $avgMemory / 1024,
                'errors' => array_filter(array_column($executions, 'error'))
            ];
        }

        return $report;
    }
}
```

## Error Handling Patterns

### 1. Graceful Degradation

```php
class GracefulJsonMapper
{
    private JsonDataMapper $mapper;
    private array $fallbackValues;

    public function __construct(JsonDataMapper $mapper, array $fallbackValues = [])
    {
        $this->mapper = $mapper;
        $this->fallbackValues = $fallbackValues;
    }

    public function safeTransform(string $json): array
    {
        try {
            return $this->mapper->transformJson($json);
        } catch (InvalidArgumentException $e) {
            logger()->warning('JSON transformation failed, using fallback', [
                'error' => $e->getMessage(),
                'json_preview' => substr($json, 0, 200)
            ]);
            
            return array_merge([
                'error' => true,
                'error_message' => 'Transformation failed',
                'processed_at' => date('c')
            ], $this->fallbackValues);
        }
    }
}

// Usage
$gracefulMapper = new GracefulJsonMapper($mapper, [
    'id' => null,
    'name' => 'Unknown',
    'status' => 'error'
]);

$result = $gracefulMapper->safeTransform($possiblyBadJson);
```

### 2. Validation Pipeline

```php
class ValidationPipeline
{
    private array $validators = [];

    public function addValidator(callable $validator, string $message): self
    {
        $this->validators[] = ['validator' => $validator, 'message' => $message];
        return $this;
    }

    public function validate(array $data): array
    {
        $errors = [];
        
        foreach ($this->validators as $rule) {
            try {
                if (!$rule['validator']($data)) {
                    $errors[] = $rule['message'];
                }
            } catch (Exception $e) {
                $errors[] = "Validation error: " . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException("Validation failed: " . implode(', ', $errors));
        }

        return $data;
    }
}

// Usage
$validator = new ValidationPipeline();
$validator
    ->addValidator(fn($data) => isset($data['id']), 'ID is required')
    ->addValidator(fn($data) => is_numeric($data['id'] ?? null), 'ID must be numeric')
    ->addValidator(fn($data) => !empty($data['email'] ?? ''), 'Email is required')
    ->addValidator(fn($data) => filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL), 'Email must be valid');

try {
    $validatedData = $validator->validate($sourceData);
    $result = $mapper->transformArray($validatedData);
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    return ['error' => $e->getMessage()];
}
```

This troubleshooting guide should help you resolve most common issues you'll encounter when working with JsonDataMapper. Remember to always test your transformations with sample data before processing large datasets in production.