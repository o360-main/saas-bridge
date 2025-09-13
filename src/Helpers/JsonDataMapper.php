<?php

namespace O360Main\SaasBridge\Helpers;

use InvalidArgumentException;

class JsonDataMapper
{
    private array $mappingRules = [];
    private array $defaultValues = [];
    private array $transformers = [];
    private bool $strictMode = false;
    private array $arrayMappings = [];
    private array $nestedMappings = [];

    public function __construct(array $mappingRules = [], bool $strictMode = false)
    {
        $this->mappingRules = $mappingRules;
        $this->strictMode = $strictMode;
    }

    /**
     * Create a new mapper instance
     */
    public static function create(array $mappingRules = []): self
    {
        return new self($mappingRules);
    }

    /**
     * Set mapping rules for field transformation
     * 
     * @param array $rules Format: ['target_field' => 'source_field'] or ['target_field' => ['source' => 'source_field', 'transform' => 'transformer']]
     */
    public function setMappingRules(array $rules): self
    {
        $this->mappingRules = $rules;
        return $this;
    }

    /**
     * Add a single mapping rule
     */
    public function addMapping(string $targetField, string|array $sourceConfig): self
    {
        $this->mappingRules[$targetField] = $sourceConfig;
        return $this;
    }

    /**
     * Set default values for fields
     */
    public function setDefaults(array $defaults): self
    {
        $this->defaultValues = $defaults;
        return $this;
    }

    /**
     * Add a default value for a field
     */
    public function addDefault(string $field, mixed $value): self
    {
        $this->defaultValues[$field] = $value;
        return $this;
    }

    /**
     * Register custom transformers
     */
    public function addTransformer(string $name, callable $transformer): self
    {
        $this->transformers[$name] = $transformer;
        return $this;
    }

    /**
     * Enable/disable strict mode (throw exceptions for missing fields)
     */
    public function strictMode(bool $enabled = true): self
    {
        $this->strictMode = $enabled;
        return $this;
    }

    /**
     * Transform JSON string to another JSON structure
     */
    public function transformJson(string $sourceJson): string
    {
        $sourceData = json_decode($sourceJson, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON input: ' . json_last_error_msg());
        }

        $transformed = $this->transformArray($sourceData);
        
        return json_encode($transformed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Transform JSON string to PHP array
     */
    public function transformJsonToArray(string $sourceJson): array
    {
        $sourceData = json_decode($sourceJson, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON input: ' . json_last_error_msg());
        }

        return $this->transformArray($sourceData);
    }

    /**
     * Transform PHP array to JSON string
     */
    public function transformArrayToJson(array $sourceData): string
    {
        $transformed = $this->transformArray($sourceData);
        return json_encode($transformed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Add array mapping for transforming nested arrays
     * 
     * @param string $targetField Target field name
     * @param string $sourceField Source array field
     * @param array $itemMapping Mapping rules for each array item
     */
    public function addArrayMapping(string $targetField, string $sourceField, array $itemMapping): self
    {
        $this->arrayMappings[$targetField] = [
            'source' => $sourceField,
            'mapping' => $itemMapping
        ];
        return $this;
    }

    /**
     * Add nested object mapping
     * 
     * @param string $targetField Target field name
     * @param string $sourceField Source object field
     * @param array $nestedMapping Mapping rules for nested object
     */
    public function addNestedMapping(string $targetField, string $sourceField, array $nestedMapping): self
    {
        $this->nestedMappings[$targetField] = [
            'source' => $sourceField,
            'mapping' => $nestedMapping
        ];
        return $this;
    }

    /**
     * Flatten a multidimensional array
     */
    private function flattenArray(array $array): array
    {
        $result = [];
        foreach ($array as $item) {
            if (is_array($item)) {
                $result = array_merge($result, $this->flattenArray($item));
            } else {
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * Map array values with a specific field extraction
     * 
     * @param string $targetField Target field name
     * @param string $sourceField Source array field
     * @param string $extractField Field to extract from each array item
     * @param string|null $transform Optional transformer to apply
     */
    public function addArrayExtraction(string $targetField, string $sourceField, string $extractField, ?string $transform = null): self
    {
        $this->arrayMappings[$targetField] = [
            'source' => $sourceField,
            'extract' => $extractField,
            'transform' => $transform
        ];
        return $this;
    }

    /**
     * Add array filtering
     * 
     * @param string $targetField Target field name
     * @param string $sourceField Source array field
     * @param callable $filter Filter function
     */
    public function addArrayFilter(string $targetField, string $sourceField, callable $filter): self
    {
        $this->arrayMappings[$targetField] = [
            'source' => $sourceField,
            'filter' => $filter
        ];
        return $this;
    }

    /**
     * Transform array with array and nested mappings support
     */
    public function transformArray(array $sourceData): array
    {
        $result = [];

        // Apply regular mapping rules
        foreach ($this->mappingRules as $targetField => $sourceConfig) {
            $value = $this->extractValue($sourceData, $sourceConfig);
            
            if ($value !== null) {
                $this->setNestedValue($result, $targetField, $value);
            } elseif ($this->strictMode && !isset($this->defaultValues[$targetField])) {
                throw new InvalidArgumentException("Required field '{$targetField}' not found in source data");
            }
        }

        // Apply array mappings
        foreach ($this->arrayMappings as $targetField => $config) {
            $sourceArray = $this->getNestedValue($sourceData, $config['source']);
            
            if (is_array($sourceArray)) {
                $transformedArray = $this->processArrayMapping($sourceArray, $config);
                $this->setNestedValue($result, $targetField, $transformedArray);
            }
        }

        // Apply nested mappings
        foreach ($this->nestedMappings as $targetField => $config) {
            $sourceObject = $this->getNestedValue($sourceData, $config['source']);
            
            if (is_array($sourceObject)) {
                $nestedMapper = new self($config['mapping'], $this->strictMode);
                $nestedMapper->transformers = $this->transformers;
                $transformedObject = $nestedMapper->transformArray($sourceObject);
                $this->setNestedValue($result, $targetField, $transformedObject);
            }
        }

        // Apply default values
        foreach ($this->defaultValues as $field => $defaultValue) {
            if (!$this->hasNestedValue($result, $field)) {
                $this->setNestedValue($result, $field, $defaultValue);
            }
        }

        return $result;
    }

    /**
     * Process array mapping with different strategies
     */
    private function processArrayMapping(array $sourceArray, array $config): array
    {
        // Handle array extraction
        if (isset($config['extract'])) {
            return $this->extractFromArray($sourceArray, $config['extract'], $config['transform'] ?? null);
        }
        
        // Handle array filtering
        if (isset($config['filter'])) {
            return array_filter($sourceArray, $config['filter']);
        }
        
        // Handle standard mapping
        if (isset($config['mapping'])) {
            return $this->transformArrayItems($sourceArray, $config['mapping']);
        }
        
        return $sourceArray;
    }
    
    /**
     * Extract specific field from array items
     */
    private function extractFromArray(array $sourceArray, string $extractField, ?string $transform = null): array
    {
        $result = [];
        
        foreach ($sourceArray as $item) {
            if (is_array($item) && isset($item[$extractField])) {
                $value = $item[$extractField];
                
                if ($transform) {
                    if (isset($this->transformers[$transform])) {
                        $value = $this->transformers[$transform]($value);
                    } else {
                        $value = $this->applyBuiltInTransformer($value, $transform);
                    }
                }
                
                $result[] = $value;
            }
        }
        
        return $result;
    }

    /**
     * Transform array items using mapping rules
     */
    private function transformArrayItems(array $sourceArray, array $itemMapping): array
    {
        $result = [];
        
        foreach ($sourceArray as $index => $item) {
            if (is_array($item)) {
                // Transform object item
                $itemMapper = new self($itemMapping, $this->strictMode);
                $itemMapper->transformers = $this->transformers;
                $result[$index] = $itemMapper->transformArray($item);
            } else {
                // Transform primitive item
                $result[$index] = $this->transformPrimitiveItem($item, $itemMapping);
            }
        }
        
        return $result;
    }

    /**
     * Transform primitive array item
     */
    private function transformPrimitiveItem(mixed $item, array $mapping): mixed
    {
        // For primitive items, mapping should specify transformation
        if (isset($mapping['transform'])) {
            if (isset($this->transformers[$mapping['transform']])) {
                return $this->transformers[$mapping['transform']]($item);
            }
            return $this->applyBuiltInTransformer($item, $mapping['transform']);
        }
        
        return $item;
    }

    /**
     * Extract value from source data based on configuration
     */
    private function extractValue(array $sourceData, string|array $config): mixed
    {
        if (is_string($config)) {
            // Simple field mapping
            return $this->getNestedValue($sourceData, $config);
        }

        if (is_array($config)) {
            $sourceField = $config['source'] ?? null;
            $transformer = $config['transform'] ?? null;
            $defaultValue = $config['default'] ?? null;

            if (!$sourceField) {
                return $defaultValue;
            }

            $value = $this->getNestedValue($sourceData, $sourceField);

            if ($value === null) {
                return $defaultValue;
            }

            // Apply transformer if specified
            if ($transformer && isset($this->transformers[$transformer])) {
                return $this->transformers[$transformer]($value);
            }

            // Built-in transformers
            if ($transformer) {
                $value = $this->applyBuiltInTransformer($value, $transformer);
            }

            return $value;
        }

        return null;
    }

    /**
     * Apply built-in transformers
     */
    private function applyBuiltInTransformer(mixed $value, string $transformer): mixed
    {
        return match($transformer) {
            'string' => (string) $value,
            'int', 'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'bool', 'boolean' => (bool) $value,
            'array' => (array) $value,
            'uppercase' => strtoupper((string) $value),
            'lowercase' => strtolower((string) $value),
            'trim' => trim((string) $value),
            'date_iso' => date('c', is_numeric($value) ? $value : strtotime($value)),
            'date_format' => date('Y-m-d', is_numeric($value) ? $value : strtotime($value)),
            'timestamp' => is_numeric($value) ? $value : strtotime($value),
            'json_decode' => json_decode($value, true),
            'json_encode' => json_encode($value),
            'null_if_empty' => empty($value) ? null : $value,
            'split_comma' => explode(',', (string) $value),
            'join_comma' => is_array($value) ? implode(',', $value) : $value,
            'array_first' => is_array($value) ? ($value[0] ?? null) : $value,
            'array_last' => is_array($value) ? ($value[array_key_last($value)] ?? null) : $value,
            'array_count' => is_array($value) ? count($value) : (is_countable($value) ? count($value) : 0),
            'array_values' => is_array($value) ? array_values($value) : [$value],
            'array_keys' => is_array($value) ? array_keys($value) : [],
            'array_unique' => is_array($value) ? array_unique($value) : [$value],
            'array_flatten' => is_array($value) ? $this->flattenArray($value) : [$value],
            'implode_space' => is_array($value) ? implode(' ', $value) : $value,
            'implode_pipe' => is_array($value) ? implode('|', $value) : $value,
            'array_filter_empty' => is_array($value) ? array_filter($value, fn($item) => !empty($item)) : $value,
            default => $value
        };
    }

    /**
     * Get nested value using dot notation with array index support
     */
    private function getNestedValue(array $data, string $path): mixed
    {
        $keys = explode('.', $path);
        $current = $data;

        foreach ($keys as $key) {
            // Handle array indices like "items[0]" or "items[*]"
            if (preg_match('/^(\w+)\[(\d+|\*)\]$/', $key, $matches)) {
                $arrayKey = $matches[1];
                $index = $matches[2];
                
                if (!is_array($current) || !array_key_exists($arrayKey, $current)) {
                    return null;
                }
                
                $array = $current[$arrayKey];
                if (!is_array($array)) {
                    return null;
                }
                
                if ($index === '*') {
                    // Return all items
                    $current = $array;
                } else {
                    // Return specific index
                    $indexNum = (int)$index;
                    if (!array_key_exists($indexNum, $array)) {
                        return null;
                    }
                    $current = $array[$indexNum];
                }
            } else {
                // Regular key
                if (!is_array($current) || !array_key_exists($key, $current)) {
                    return null;
                }
                $current = $current[$key];
            }
        }

        return $current;
    }

    /**
     * Set nested value using dot notation
     */
    private function setNestedValue(array &$data, string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        $current = &$data;

        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                $current[$key] = $value;
            } else {
                if (!isset($current[$key]) || !is_array($current[$key])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }
        }
    }

    /**
     * Check if nested value exists using dot notation
     */
    private function hasNestedValue(array $data, string $path): bool
    {
        $keys = explode('.', $path);
        $current = $data;

        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return false;
            }
            $current = $current[$key];
        }

        return true;
    }

    /**
     * Transform array of objects (useful for collections)
     */
    public function transformCollection(array $sourceCollection): array
    {
        return array_map([$this, 'transformArray'], $sourceCollection);
    }

    /**
     * Create mapper from configuration file
     */
    public static function fromConfig(string $configPath): self
    {
        if (!file_exists($configPath)) {
            throw new InvalidArgumentException("Configuration file not found: {$configPath}");
        }

        $config = require $configPath;
        
        if (!is_array($config)) {
            throw new InvalidArgumentException("Configuration file must return an array");
        }

        $mapper = new self(
            $config['mapping'] ?? [],
            $config['strict_mode'] ?? false
        );

        if (isset($config['defaults'])) {
            $mapper->setDefaults($config['defaults']);
        }

        if (isset($config['transformers'])) {
            foreach ($config['transformers'] as $name => $transformer) {
                $mapper->addTransformer($name, $transformer);
            }
        }

        return $mapper;
    }

    /**
     * Quick transform method for simple mappings
     */
    public static function quickTransform(string $json, array $mappingRules): string
    {
        return self::create($mappingRules)->transformJson($json);
    }

    /**
     * Get mapping rules
     */
    public function getMappingRules(): array
    {
        return $this->mappingRules;
    }

    /**
     * Get default values
     */
    public function getDefaults(): array
    {
        return $this->defaultValues;
    }

    /**
     * Get registered transformers
     */
    public function getTransformers(): array
    {
        return array_keys($this->transformers);
    }
}