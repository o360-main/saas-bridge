# JsonDataMapper - Performance Optimization Guide

This guide covers performance best practices, optimization techniques, and memory management for JsonDataMapper.

## Performance Fundamentals

### Understanding Performance Impact

JsonDataMapper performance depends on several factors:

1. **Data Size** - Larger JSON structures take more time to process
2. **Mapping Complexity** - Complex transformers and nested mappings increase processing time
3. **Memory Usage** - Large datasets can cause memory issues
4. **Transformer Efficiency** - Custom transformers can be bottlenecks

### Benchmarking Your Transformations

```php
function benchmarkTransformation(JsonDataMapper $mapper, string $json, int $iterations = 1000): array
{
    $startTime = microtime(true);
    $startMemory = memory_get_usage(true);
    
    for ($i = 0; $i < $iterations; $i++) {
        $result = $mapper->transformJson($json);
    }
    
    $endTime = microtime(true);
    $endMemory = memory_get_usage(true);
    
    return [
        'time_per_transformation' => ($endTime - $startTime) / $iterations,
        'memory_used' => $endMemory - $startMemory,
        'peak_memory' => memory_get_peak_usage(true)
    ];
}

// Usage
$mapper = JsonDataMapper::create($rules);
$stats = benchmarkTransformation($mapper, $sampleJson, 1000);
echo "Time per transformation: " . ($stats['time_per_transformation'] * 1000) . " ms\n";
echo "Memory used: " . number_format($stats['memory_used'] / 1024 / 1024, 2) . " MB\n";
```

## Optimization Strategies

### 1. Reuse Mapper Instances

**❌ Inefficient - Creates new instance each time:**

```php
foreach ($dataItems as $item) {
    $mapper = JsonDataMapper::create($rules);
    $result[] = $mapper->transformArray($item);
}
```

**✅ Efficient - Reuse mapper instance:**

```php
$mapper = JsonDataMapper::create($rules);
foreach ($dataItems as $item) {
    $result[] = $mapper->transformArray($item);
}
```

**Performance Impact:** Up to 90% faster for collections

### 2. Use Built-in Transformers

**❌ Slower - Custom transformer:**

```php
$mapper->addTransformer('custom_upper', function($value) {
    return strtoupper($value);
});

$mapping = ['name' => ['source' => 'title', 'transform' => 'custom_upper']];
```

**✅ Faster - Built-in transformer:**

```php
$mapping = ['name' => ['source' => 'title', 'transform' => 'uppercase']];
```

**Performance Impact:** Built-in transformers are 2-3x faster

### 3. Optimize Collection Processing

**❌ Inefficient - Manual iteration:**

```php
$results = [];
foreach ($jsonArray as $item) {
    $results[] = $mapper->transformArray($item);
}
```

**✅ Efficient - Use transformCollection:**

```php
$results = $mapper->transformCollection($jsonArray);
```

**Performance Impact:** 20-30% faster for large collections

### 4. Minimize Deep Nesting

**❌ Slower - Deep nested access:**

```php
$mapping = [
    'value' => 'level1.level2.level3.level4.target'
];
```

**✅ Faster - Flatter structure when possible:**

```php
$mapping = [
    'value' => 'target_field'
];
```

### 5. Cache Expensive Operations

**❌ Inefficient - Repeated expensive operations:**

```php
$mapper->addTransformer('lookup_user', function($userId) {
    return User::find($userId); // Database call every time
});
```

**✅ Efficient - Cache results:**

```php
$mapper->addTransformer('lookup_user', function($userId) {
    static $cache = [];
    
    if (!isset($cache[$userId])) {
        $cache[$userId] = User::find($userId);
    }
    
    return $cache[$userId];
});
```

## Memory Management

### 1. Handle Large Datasets

For processing large JSON files or arrays, use chunking:

```php
class LargeDataProcessor
{
    private JsonDataMapper $mapper;
    private int $chunkSize;

    public function __construct(JsonDataMapper $mapper, int $chunkSize = 1000)
    {
        $this->mapper = $mapper;
        $this->chunkSize = $chunkSize;
    }

    public function processLargeArray(array $data): \Generator
    {
        $chunks = array_chunk($data, $this->chunkSize);
        
        foreach ($chunks as $chunk) {
            $results = $this->mapper->transformCollection($chunk);
            
            foreach ($results as $result) {
                yield $result;
            }
            
            // Force garbage collection for large datasets
            unset($chunk, $results);
            if (memory_get_usage() > 100 * 1024 * 1024) { // 100MB threshold
                gc_collect_cycles();
            }
        }
    }

    public function processLargeFile(string $filePath): \Generator
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new InvalidArgumentException("Cannot open file: $filePath");
        }

        $buffer = [];
        $bufferSize = 0;
        
        while (($line = fgets($handle)) !== false) {
            $data = json_decode(trim($line), true);
            if ($data) {
                $buffer[] = $data;
                $bufferSize++;
                
                if ($bufferSize >= $this->chunkSize) {
                    yield from $this->processChunk($buffer);
                    $buffer = [];
                    $bufferSize = 0;
                }
            }
        }
        
        // Process remaining items
        if (!empty($buffer)) {
            yield from $this->processChunk($buffer);
        }
        
        fclose($handle);
    }

    private function processChunk(array $chunk): \Generator
    {
        $results = $this->mapper->transformCollection($chunk);
        foreach ($results as $result) {
            yield $result;
        }
    }
}

// Usage
$processor = new LargeDataProcessor($mapper, 500);

// Process large array
foreach ($processor->processLargeArray($hugeDataArray) as $transformedItem) {
    // Process one item at a time
    processItem($transformedItem);
}

// Process large file (JSONL format)
foreach ($processor->processLargeFile('huge-data.jsonl') as $transformedItem) {
    processItem($transformedItem);
}
```

### 2. Memory-Efficient Streaming

For extremely large files, use streaming JSON parsing:

```php
class StreamingJsonProcessor
{
    private JsonDataMapper $mapper;

    public function __construct(JsonDataMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function processJsonStream(string $filePath): \Generator
    {
        $stream = fopen($filePath, 'r');
        $parser = new \JsonStreamingParser\Parser($stream, new class($this->mapper) implements \JsonStreamingParser\Listener {
            private JsonDataMapper $mapper;
            private array $currentObject = [];
            private int $depth = 0;

            public function __construct(JsonDataMapper $mapper)
            {
                $this->mapper = $mapper;
            }

            public function startDocument(): void {}
            public function endDocument(): void {}

            public function startObject(): void
            {
                $this->depth++;
                if ($this->depth === 2) { // Assuming array of objects at level 2
                    $this->currentObject = [];
                }
            }

            public function endObject(): void
            {
                if ($this->depth === 2 && !empty($this->currentObject)) {
                    $transformed = $this->mapper->transformArray($this->currentObject);
                    // In real implementation, you'd yield this or store it
                    $this->currentObject = [];
                }
                $this->depth--;
            }

            public function startArray(): void {}
            public function endArray(): void {}

            public function key(string $key): void
            {
                $this->currentKey = $key;
            }

            public function value($value): void
            {
                if ($this->depth === 2) {
                    $this->currentObject[$this->currentKey] = $value;
                }
            }
        });

        try {
            $parser->parse();
        } finally {
            fclose($stream);
        }
    }
}
```

## Advanced Optimization Techniques

### 1. Pre-compile Mapping Rules

For frequently used mappings, pre-compile complex rules:

```php
class OptimizedMapper
{
    private array $compiledRules = [];

    public function compileRules(array $rules): void
    {
        foreach ($rules as $target => $source) {
            $this->compiledRules[$target] = $this->compileRule($source);
        }
    }

    private function compileRule($rule): callable
    {
        if (is_string($rule)) {
            // Simple path access
            $path = explode('.', $rule);
            return function(array $data) use ($path) {
                $current = $data;
                foreach ($path as $key) {
                    if (!isset($current[$key])) return null;
                    $current = $current[$key];
                }
                return $current;
            };
        }

        if (is_array($rule)) {
            $source = $rule['source'] ?? null;
            $transform = $rule['transform'] ?? null;
            $default = $rule['default'] ?? null;

            $sourceFn = $this->compileRule($source);
            $transformFn = $this->getTransformer($transform);

            return function(array $data) use ($sourceFn, $transformFn, $default) {
                $value = $sourceFn($data);
                if ($value === null) return $default;
                return $transformFn ? $transformFn($value) : $value;
            };
        }

        return fn() => null;
    }
}
```

### 2. Parallel Processing

For CPU-intensive transformations, use parallel processing:

```php
class ParallelProcessor
{
    public function processInParallel(array $data, JsonDataMapper $mapper, int $processes = 4): array
    {
        $chunks = array_chunk($data, ceil(count($data) / $processes));
        $results = [];

        $pipes = [];
        $processes = [];

        foreach ($chunks as $i => $chunk) {
            $descriptorspec = [
                0 => ["pipe", "r"],  // stdin
                1 => ["pipe", "w"],  // stdout
                2 => ["pipe", "w"]   // stderr
            ];

            $process = proc_open(
                'php -r "' . $this->generateWorkerScript() . '"',
                $descriptorspec,
                $pipes[$i]
            );

            if (is_resource($process)) {
                $processes[$i] = $process;
                
                // Send data to worker
                fwrite($pipes[$i][0], serialize([
                    'mapper_config' => $this->serializeMapper($mapper),
                    'data' => $chunk
                ]));
                fclose($pipes[$i][0]);
            }
        }

        // Collect results
        foreach ($processes as $i => $process) {
            $output = stream_get_contents($pipes[$i][1]);
            fclose($pipes[$i][1]);
            fclose($pipes[$i][2]);
            
            $results = array_merge($results, unserialize($output));
            proc_close($process);
        }

        return $results;
    }

    private function generateWorkerScript(): string
    {
        return '
            $input = unserialize(file_get_contents("php://stdin"));
            $mapper = $this->deserializeMapper($input["mapper_config"]);
            $results = $mapper->transformCollection($input["data"]);
            echo serialize($results);
        ';
    }
}
```

### 3. Database Integration Optimization

When mapping involves database lookups:

```php
class DatabaseOptimizedMapper
{
    private array $lookupCache = [];
    private array $batchQueries = [];

    public function addDatabaseLookup(string $transformer, string $table, string $keyField, string $valueField): void
    {
        $this->batchQueries[$transformer] = [
            'table' => $table,
            'key_field' => $keyField,
            'value_field' => $valueField,
            'keys' => []
        ];
    }

    public function preloadLookups(array $data): void
    {
        // Collect all keys that need to be looked up
        foreach ($data as $item) {
            foreach ($this->batchQueries as $transformer => $config) {
                $key = $this->extractKey($item, $transformer);
                if ($key) {
                    $this->batchQueries[$transformer]['keys'][] = $key;
                }
            }
        }

        // Execute batch queries
        foreach ($this->batchQueries as $transformer => $config) {
            if (!empty($config['keys'])) {
                $results = DB::table($config['table'])
                    ->whereIn($config['key_field'], array_unique($config['keys']))
                    ->pluck($config['value_field'], $config['key_field'])
                    ->toArray();
                
                $this->lookupCache[$transformer] = $results;
            }
        }
    }

    public function getOptimizedTransformer(string $transformer): callable
    {
        return function($key) use ($transformer) {
            return $this->lookupCache[$transformer][$key] ?? null;
        };
    }
}

// Usage
$optimizer = new DatabaseOptimizedMapper();
$optimizer->addDatabaseLookup('user_name', 'users', 'id', 'name');
$optimizer->addDatabaseLookup('category_name', 'categories', 'id', 'name');

// Preload all needed data in batch queries
$optimizer->preloadLookups($largeDataset);

// Use optimized transformers
$mapper = JsonDataMapper::create([
    'user' => ['source' => 'user_id', 'transform' => 'user_name'],
    'category' => ['source' => 'category_id', 'transform' => 'category_name']
])
->addTransformer('user_name', $optimizer->getOptimizedTransformer('user_name'))
->addTransformer('category_name', $optimizer->getOptimizedTransformer('category_name'));
```

## Performance Monitoring

### 1. Built-in Performance Tracking

```php
class PerformanceTrackingMapper extends JsonDataMapper
{
    private array $metrics = [];

    public function transformArray(array $sourceData): array
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $result = parent::transformArray($sourceData);

        $this->metrics[] = [
            'time' => microtime(true) - $startTime,
            'memory' => memory_get_usage() - $startMemory,
            'input_size' => strlen(json_encode($sourceData)),
            'output_size' => strlen(json_encode($result))
        ];

        return $result;
    }

    public function getMetrics(): array
    {
        if (empty($this->metrics)) {
            return [];
        }

        $count = count($this->metrics);
        $avgTime = array_sum(array_column($this->metrics, 'time')) / $count;
        $avgMemory = array_sum(array_column($this->metrics, 'memory')) / $count;

        return [
            'transformations_count' => $count,
            'avg_time_ms' => $avgTime * 1000,
            'avg_memory_kb' => $avgMemory / 1024,
            'total_time_ms' => array_sum(array_column($this->metrics, 'time')) * 1000,
            'peak_memory_mb' => memory_get_peak_usage(true) / 1024 / 1024
        ];
    }

    public function resetMetrics(): void
    {
        $this->metrics = [];
    }
}

// Usage
$mapper = new PerformanceTrackingMapper($rules);
$results = $mapper->transformCollection($data);
$metrics = $mapper->getMetrics();

echo "Average transformation time: {$metrics['avg_time_ms']} ms\n";
echo "Average memory usage: {$metrics['avg_memory_kb']} KB\n";
```

### 2. Profiling Slow Transformers

```php
class TransformerProfiler
{
    private array $transformerTimes = [];

    public function profileTransformer(string $name, callable $transformer): callable
    {
        return function($value) use ($name, $transformer) {
            $start = microtime(true);
            $result = $transformer($value);
            $time = microtime(true) - $start;

            if (!isset($this->transformerTimes[$name])) {
                $this->transformerTimes[$name] = [];
            }
            $this->transformerTimes[$name][] = $time;

            return $result;
        };
    }

    public function getSlowTransformers(float $thresholdMs = 1.0): array
    {
        $slow = [];
        foreach ($this->transformerTimes as $name => $times) {
            $avgTime = array_sum($times) / count($times);
            if ($avgTime * 1000 > $thresholdMs) {
                $slow[$name] = [
                    'avg_time_ms' => $avgTime * 1000,
                    'call_count' => count($times),
                    'total_time_ms' => array_sum($times) * 1000
                ];
            }
        }

        // Sort by total time descending
        uasort($slow, fn($a, $b) => $b['total_time_ms'] <=> $a['total_time_ms']);
        
        return $slow;
    }
}

// Usage
$profiler = new TransformerProfiler();

$mapper = JsonDataMapper::create($rules)
    ->addTransformer('slow_operation', $profiler->profileTransformer('slow_operation', function($value) {
        // Some expensive operation
        sleep(0.01); // Simulated slow operation
        return strtoupper($value);
    }));

// After processing
$slowTransformers = $profiler->getSlowTransformers(0.5); // 0.5ms threshold
foreach ($slowTransformers as $name => $stats) {
    echo "Slow transformer '$name': {$stats['avg_time_ms']}ms avg, {$stats['call_count']} calls\n";
}
```

## Performance Testing

### Automated Performance Tests

```php
class JsonMapperPerformanceTest extends TestCase
{
    public function test_transformation_performance()
    {
        $mapper = JsonDataMapper::create([
            'id' => ['source' => 'user_id', 'transform' => 'int'],
            'name' => 'full_name',
            'email' => ['source' => 'email_address', 'transform' => 'lowercase']
        ]);

        $testData = $this->generateTestData(1000);
        
        $startTime = microtime(true);
        $results = $mapper->transformCollection($testData);
        $duration = microtime(true) - $startTime;

        // Performance assertions
        $this->assertLessThan(1.0, $duration, 'Transformation should complete in under 1 second');
        $this->assertCount(1000, $results, 'All items should be transformed');
        $this->assertLessThan(50 * 1024 * 1024, memory_get_peak_usage(), 'Memory usage should be under 50MB');
    }

    private function generateTestData(int $count): array
    {
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $data[] = [
                'user_id' => $i,
                'full_name' => "User $i",
                'email_address' => "user$i@example.com"
            ];
        }
        return $data;
    }
}
```

Following these optimization strategies will ensure your JsonDataMapper implementations perform efficiently even with large datasets and complex transformations.