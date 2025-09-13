<?php

namespace O360Main\SaasBridge\Helpers;

use O360Main\SaasBridge\Helpers\JsonDataMapper;
use Illuminate\Http\Client\Response;
use InvalidArgumentException;

class HttpDataMapper extends JsonDataMapper
{
    /**
     * Transform Laravel HTTP Response to array
     */
    public function transformResponse(Response $response): array
    {
        if (!$response->successful()) {
            throw new InvalidArgumentException(
                "HTTP request failed with status {$response->status()}: {$response->body()}"
            );
        }

        $data = $response->json();
        
        if ($data === null) {
            throw new InvalidArgumentException('Response body is not valid JSON');
        }

        return $this->transformArray($data);
    }

    /**
     * Transform Laravel HTTP Response to JSON string
     */
    public function transformResponseToJson(Response $response): string
    {
        $transformed = $this->transformResponse($response);
        return json_encode($transformed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Transform multiple HTTP responses (useful for paginated APIs)
     */
    public function transformResponseCollection(array $responses): array
    {
        $results = [];
        
        foreach ($responses as $response) {
            if ($response instanceof Response) {
                $results[] = $this->transformResponse($response);
            } else {
                throw new InvalidArgumentException('All items must be HTTP Response instances');
            }
        }
        
        return $results;
    }

    /**
     * Transform paginated API response with automatic page handling
     */
    public function transformPaginatedResponse(
        Response $response, 
        ?string $dataPath = null,
        ?string $metaPath = 'meta'
    ): array {
        $responseData = $this->transformResponse($response);
        
        $items = $dataPath ? $this->getNestedValue($responseData, $dataPath) : $responseData;
        $meta = $metaPath ? $this->getNestedValue($responseData, $metaPath) : [];
        
        return [
            'data' => is_array($items) ? $items : [$items],
            'meta' => $meta ?? [],
            'transformed_at' => date('c')
        ];
    }

    /**
     * Extract and transform specific fields from HTTP response headers
     */
    public function extractResponseHeaders(Response $response, array $headerMappings = []): array
    {
        $headers = $response->headers();
        $extracted = [];
        
        foreach ($headerMappings as $targetKey => $headerName) {
            $value = $headers[$headerName] ?? null;
            
            if (is_array($value)) {
                $value = $value[0] ?? null; // Get first value if multiple
            }
            
            $extracted[$targetKey] = $value;
        }
        
        return $extracted;
    }

    /**
     * Transform response with additional metadata (headers, status, etc.)
     */
    public function transformResponseWithMeta(Response $response, array $headerMappings = []): array
    {
        $transformedData = $this->transformResponse($response);
        $headers = $this->extractResponseHeaders($response, $headerMappings);
        
        return [
            'data' => $transformedData,
            'http_meta' => [
                'status' => $response->status(),
                'headers' => $headers,
                'response_time' => $this->getResponseTime($response),
                'content_type' => $response->header('Content-Type'),
                'content_length' => $response->header('Content-Length')
            ],
            'transformed_at' => date('c')
        ];
    }

    /**
     * Handle API error responses with transformation
     */
    public function transformErrorResponse(Response $response): array
    {
        $body = $response->body();
        $jsonData = $response->json();
        
        // If response has JSON error data, transform it
        if ($jsonData && is_array($jsonData)) {
            $transformedError = $this->transformArray($jsonData);
        } else {
            $transformedError = ['message' => $body];
        }
        
        return [
            'error' => true,
            'http_status' => $response->status(),
            'error_data' => $transformedError,
            'raw_body' => $body,
            'occurred_at' => date('c')
        ];
    }

    /**
     * Create mapper specifically for API endpoint responses
     */
    public static function forApiEndpoint(string $endpoint, array $mappingRules = []): self
    {
        $mapper = new self($mappingRules);
        
        // Add common API transformers
        $mapper->addTransformer('api_timestamp', function($timestamp) {
            if (is_numeric($timestamp)) {
                return date('c', $timestamp);
            }
            return date('c', strtotime($timestamp));
        });
        
        $mapper->addTransformer('api_boolean', function($value) {
            if (is_string($value)) {
                return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
            }
            return (bool) $value;
        });
        
        $mapper->addTransformer('api_money', function($value) {
            // Convert cents to dollars or handle decimal strings
            if (is_string($value) && strpos($value, '.') === false && strlen($value) >= 3) {
                return (float) $value / 100; // Assume cents
            }
            return (float) $value;
        });
        
        $mapper->setDefaults([
            'endpoint' => $endpoint,
            'fetched_at' => date('c')
        ]);
        
        return $mapper;
    }

    /**
     * Batch transform multiple API responses with error handling
     */
    public function batchTransformResponses(array $responses, bool $skipErrors = true): array
    {
        $results = [];
        $errors = [];
        
        foreach ($responses as $key => $response) {
            try {
                if ($response instanceof Response) {
                    if ($response->successful()) {
                        $results[$key] = $this->transformResponse($response);
                    } else {
                        $errorData = $this->transformErrorResponse($response);
                        
                        if ($skipErrors) {
                            $errors[$key] = $errorData;
                        } else {
                            throw new InvalidArgumentException(
                                "Response $key failed: " . $errorData['error_data']['message'] ?? 'Unknown error'
                            );
                        }
                    }
                } else {
                    throw new InvalidArgumentException("Item $key is not a valid HTTP Response");
                }
            } catch (\Exception $e) {
                if ($skipErrors) {
                    $errors[$key] = [
                        'error' => true,
                        'message' => $e->getMessage(),
                        'occurred_at' => date('c')
                    ];
                } else {
                    throw $e;
                }
            }
        }
        
        return [
            'successful' => $results,
            'failed' => $errors,
            'summary' => [
                'total' => count($responses),
                'successful' => count($results),
                'failed' => count($errors)
            ]
        ];
    }

    /**
     * Transform webhook payload (similar to HTTP response but for incoming data)
     */
    public function transformWebhookPayload(array $payload, array $headers = []): array
    {
        $transformed = $this->transformArray($payload);
        
        return [
            'data' => $transformed,
            'webhook_meta' => [
                'headers' => $headers,
                'received_at' => date('c'),
                'signature' => $headers['X-Hub-Signature'] ?? $headers['x-hub-signature'] ?? null,
                'event_type' => $headers['X-Event-Type'] ?? $headers['x-event-type'] ?? null
            ]
        ];
    }

    /**
     * Smart transform that handles both arrays and HTTP responses
     */
    public function smartTransform($input): array
    {
        if ($input instanceof Response) {
            return $this->transformResponse($input);
        } elseif (is_array($input)) {
            return $this->transformArray($input);
        } elseif (is_string($input)) {
            return $this->transformJsonToArray($input);
        } else {
            throw new InvalidArgumentException(
                'Input must be HTTP Response, array, or JSON string. Got: ' . gettype($input)
            );
        }
    }

    /**
     * Create a fluent interface for chaining HTTP transformations
     */
    public function pipe(): HttpDataMapperPipeline
    {
        return new HttpDataMapperPipeline($this);
    }

    /**
     * Get response time from response (if available)
     */
    private function getResponseTime(Response $response): ?float
    {
        // Laravel HTTP client doesn't expose response time directly
        // This is a placeholder for when/if it becomes available
        return null;
    }

    /**
     * Access to parent getNestedValue method
     */
    public function getNestedValue(array $data, string $path)
    {
        return parent::getNestedValue($data, $path);
    }
}

/**
 * Fluent pipeline for chaining HTTP data transformations
 */
class HttpDataMapperPipeline
{
    private HttpDataMapper $mapper;
    private $data;

    public function __construct(HttpDataMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Start the pipeline with input data
     */
    public function from($input): self
    {
        $this->data = $input;
        return $this;
    }

    /**
     * Transform using the configured mapper
     */
    public function transform(): self
    {
        $this->data = $this->mapper->smartTransform($this->data);
        return $this;
    }

    /**
     * Extract specific field from the data
     */
    public function extract(string $path): self
    {
        $this->data = $this->mapper->getNestedValue($this->data, $path);
        return $this;
    }

    /**
     * Filter data using a callback
     */
    public function filter(callable $callback): self
    {
        if (is_array($this->data)) {
            $this->data = array_filter($this->data, $callback);
        }
        return $this;
    }

    /**
     * Map over array data
     */
    public function map(callable $callback): self
    {
        if (is_array($this->data)) {
            $this->data = array_map($callback, $this->data);
        }
        return $this;
    }

    /**
     * Take only first N items
     */
    public function take(int $count): self
    {
        if (is_array($this->data)) {
            $this->data = array_slice($this->data, 0, $count);
        }
        return $this;
    }

    /**
     * Skip first N items
     */
    public function skip(int $count): self
    {
        if (is_array($this->data)) {
            $this->data = array_slice($this->data, $count);
        }
        return $this;
    }

    /**
     * Add additional data to the result
     */
    public function with(array $additionalData): self
    {
        if (is_array($this->data)) {
            $this->data = array_merge($this->data, $additionalData);
        }
        return $this;
    }

    /**
     * Get the final result
     */
    public function get()
    {
        return $this->data;
    }

    /**
     * Get the result as JSON string
     */
    public function toJson(): string
    {
        return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get the result as array (alias for get)
     */
    public function toArray(): array
    {
        return is_array($this->data) ? $this->data : [$this->data];
    }
}