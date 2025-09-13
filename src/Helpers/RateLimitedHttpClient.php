<?php

namespace O360Main\SaasBridge\Helpers;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use InvalidArgumentException;

class RateLimitedHttpClient
{
    private string $connectionId;
    private array $rateLimitConfig;
    private ?string $redisConnection;
    private ConnectionSyncMutex $mutex;
    private string $rateLimitKey;
    private int $maxRequests;
    private int $windowSeconds;
    private bool $blockWhenExhausted;
    private int $retryAfterSeconds;
    
    public function __construct(
        ?string $connectionId = null,
        ?array $rateLimitConfig = null,
        ?string $redisConnection = null
    ) {
        $config = config('saas-bridge.rate_limiter', []);
        $this->rateLimitConfig = array_merge($config, $rateLimitConfig ?? []);
        
        $this->connectionId = $connectionId ?? $this->getConnectionIdFromRequest();
        $this->redisConnection = $redisConnection ?? $this->rateLimitConfig['redis_connection'] ?? null;
        
        // Rate limit configuration
        $this->maxRequests = $this->rateLimitConfig['max_requests'] ?? 60;
        $this->windowSeconds = $this->rateLimitConfig['window_seconds'] ?? 60;
        $this->blockWhenExhausted = $this->rateLimitConfig['block_when_exhausted'] ?? true;
        $this->retryAfterSeconds = $this->rateLimitConfig['retry_after_seconds'] ?? 60;
        
        $keyPrefix = $this->rateLimitConfig['key_prefix'] ?? 'rate_limit';
        $this->rateLimitKey = $keyPrefix . ':' . $this->connectionId;
        
        // Initialize mutex for blocking when rate limited
        $this->mutex = new ConnectionSyncMutex(
            'rate_limit_wait:' . $this->connectionId,
            $this->connectionId
        );
        
        if (empty($this->connectionId)) {
            throw new InvalidArgumentException('Connection ID cannot be empty');
        }
    }
    
    /**
     * Create a new rate limited HTTP client with connection ID from request header
     */
    public static function make(?Request $request = null, ?array $config = null): self
    {
        $connectionId = null;
        $rateLimitConfig = config('saas-bridge.rate_limiter', []);
        $headers = $rateLimitConfig['connection_headers'] ?? ['X-Connection-ID', 'Connection-ID', 'connection-id'];
        
        // Try to get connection ID from provided request
        if ($request) {
            foreach ($headers as $header) {
                $connectionId = $request->header($header);
                if ($connectionId) {
                    break;
                }
            }
        }
        
        // Try global request if no connection ID found
        if (!$connectionId && function_exists('request')) {
            $req = request();
            foreach ($headers as $header) {
                $connectionId = $req->header($header);
                if ($connectionId) {
                    break;
                }
            }
        }
        
        return new self($connectionId, $config);
    }
    
    /**
     * Get Laravel HTTP client with rate limiting
     */
    public function client(): PendingRequest
    {
        $this->enforceRateLimit();
        
        return Http::timeout($this->rateLimitConfig['timeout'] ?? 30)
            ->retry($this->rateLimitConfig['retries'] ?? 3, $this->rateLimitConfig['retry_delay'] ?? 100);
    }
    
    /**
     * Make GET request with rate limiting
     */
    public function get(string $url, ?array $query = null): Response
    {
        return $this->client()->get($url, $query);
    }
    
    /**
     * Make POST request with rate limiting
     */
    public function post(string $url, array $data = []): Response
    {
        return $this->client()->post($url, $data);
    }
    
    /**
     * Make PUT request with rate limiting
     */
    public function put(string $url, array $data = []): Response
    {
        return $this->client()->put($url, $data);
    }
    
    /**
     * Make PATCH request with rate limiting
     */
    public function patch(string $url, array $data = []): Response
    {
        return $this->client()->patch($url, $data);
    }
    
    /**
     * Make DELETE request with rate limiting
     */
    public function delete(string $url, array $data = []): Response
    {
        return $this->client()->delete($url, $data);
    }
    
    /**
     * Make HEAD request with rate limiting
     */
    public function head(string $url, ?array $query = null): Response
    {
        return $this->client()->head($url, $query);
    }
    
    /**
     * Execute multiple requests with rate limiting (useful for batch operations)
     */
    public function batch(array $requests): array
    {
        $responses = [];
        
        foreach ($requests as $key => $requestData) {
            $method = strtoupper($requestData['method'] ?? 'GET');
            $url = $requestData['url'];
            $data = $requestData['data'] ?? [];
            
            $responses[$key] = match($method) {
                'GET' => $this->get($url, $data),
                'POST' => $this->post($url, $data),
                'PUT' => $this->put($url, $data),
                'PATCH' => $this->patch($url, $data),
                'DELETE' => $this->delete($url, $data),
                'HEAD' => $this->head($url, $data),
                default => throw new InvalidArgumentException("Unsupported HTTP method: {$method}")
            };
        }
        
        return $responses;
    }
    
    /**
     * Execute callback with rate limiting protection
     */
    public function rateLimited(callable $callback)
    {
        $this->enforceRateLimit();
        return $callback($this->client());
    }
    
    /**
     * Enforce rate limiting before making HTTP requests
     */
    private function enforceRateLimit(): void
    {
        $redis = $this->getRedis();
        $currentWindow = $this->getCurrentWindow();
        $windowKey = $this->rateLimitKey . ':' . $currentWindow;
        
        // Get current request count in this window
        $currentCount = (int) $redis->get($windowKey);
        
        // Check if rate limit is exceeded
        if ($currentCount >= $this->maxRequests) {
            if ($this->blockWhenExhausted) {
                $this->waitForRateLimit();
            } else {
                throw new \RuntimeException(
                    "Rate limit exceeded. Max {$this->maxRequests} requests per {$this->windowSeconds} seconds. Try again in {$this->getSecondsUntilReset()} seconds."
                );
            }
        }
        
        // Increment request count atomically
        $this->incrementRequestCount($windowKey);
    }
    
    /**
     * Wait for rate limit to reset using mutex
     */
    private function waitForRateLimit(): void
    {
        $waitTimeout = $this->rateLimitConfig['wait_timeout'] ?? 300; // 5 minutes max wait
        
        // Use mutex to prevent thundering herd when multiple processes are waiting
        $this->mutex->synchronized(function() {
            $redis = $this->getRedis();
            $currentWindow = $this->getCurrentWindow();
            $windowKey = $this->rateLimitKey . ':' . $currentWindow;
            
            // Double-check if we still need to wait (another process might have waited already)
            $currentCount = (int) $redis->get($windowKey);
            
            if ($currentCount >= $this->maxRequests) {
                $sleepTime = $this->getSecondsUntilReset();
                
                if ($sleepTime > 0) {
                    sleep(min($sleepTime, $this->retryAfterSeconds));
                }
            }
        }, $waitTimeout);
    }
    
    /**
     * Increment request count atomically with expiration
     */
    private function incrementRequestCount(string $windowKey): void
    {
        $redis = $this->getRedis();
        
        // Use Lua script for atomic increment with expiration
        $script = '
            local current = redis.call("INCR", KEYS[1])
            if current == 1 then
                redis.call("EXPIRE", KEYS[1], ARGV[1])
            end
            return current
        ';
        
        $redis->eval($script, 1, $windowKey, $this->windowSeconds);
    }
    
    /**
     * Get current time window for rate limiting
     */
    private function getCurrentWindow(): int
    {
        return (int) (time() / $this->windowSeconds);
    }
    
    /**
     * Get seconds until rate limit resets
     */
    private function getSecondsUntilReset(): int
    {
        $currentWindow = $this->getCurrentWindow();
        $nextWindowStart = ($currentWindow + 1) * $this->windowSeconds;
        return max(0, $nextWindowStart - time());
    }
    
    /**
     * Get current rate limit status
     */
    public function getRateLimitStatus(): array
    {
        $redis = $this->getRedis();
        $currentWindow = $this->getCurrentWindow();
        $windowKey = $this->rateLimitKey . ':' . $currentWindow;
        
        $currentCount = (int) $redis->get($windowKey);
        $remaining = max(0, $this->maxRequests - $currentCount);
        $resetTime = ($currentWindow + 1) * $this->windowSeconds;
        
        return [
            'connection_id' => $this->connectionId,
            'rate_limit_key' => $this->rateLimitKey,
            'max_requests' => $this->maxRequests,
            'window_seconds' => $this->windowSeconds,
            'current_requests' => $currentCount,
            'remaining_requests' => $remaining,
            'reset_time' => $resetTime,
            'seconds_until_reset' => $this->getSecondsUntilReset(),
            'is_exhausted' => $currentCount >= $this->maxRequests
        ];
    }
    
    /**
     * Reset rate limit for current connection (useful for testing or admin actions)
     */
    public function resetRateLimit(): bool
    {
        $redis = $this->getRedis();
        $currentWindow = $this->getCurrentWindow();
        $windowKey = $this->rateLimitKey . ':' . $currentWindow;
        
        return $redis->del($windowKey) > 0;
    }
    
    /**
     * Configure rate limiting parameters
     */
    public function configure(array $config): self
    {
        $this->rateLimitConfig = array_merge($this->rateLimitConfig, $config);
        
        if (isset($config['max_requests'])) {
            $this->maxRequests = $config['max_requests'];
        }
        
        if (isset($config['window_seconds'])) {
            $this->windowSeconds = $config['window_seconds'];
        }
        
        if (isset($config['block_when_exhausted'])) {
            $this->blockWhenExhausted = $config['block_when_exhausted'];
        }
        
        if (isset($config['retry_after_seconds'])) {
            $this->retryAfterSeconds = $config['retry_after_seconds'];
        }
        
        return $this;
    }
    
    /**
     * Create client for specific API with predefined rate limits
     */
    public static function forApi(string $apiName, ?Request $request = null): self
    {
        $apiConfigs = config('saas-bridge.api_rate_limits', []);
        $apiConfig = $apiConfigs[$apiName] ?? [];
        
        if (empty($apiConfig)) {
            throw new InvalidArgumentException("No rate limit configuration found for API: {$apiName}");
        }
        
        return self::make($request, $apiConfig);
    }
    
    /**
     * Get connection ID from current request
     */
    private function getConnectionIdFromRequest(): ?string
    {
        if (!function_exists('request')) {
            return null;
        }
        
        $request = request();
        $config = config('saas-bridge.rate_limiter', []);
        $headers = $config['connection_headers'] ?? ['X-Connection-ID', 'Connection-ID', 'connection-id'];
        
        // Try each header in priority order
        foreach ($headers as $header) {
            $connectionId = $request->header($header);
            if ($connectionId) {
                return $connectionId;
            }
        }
        
        // Fallback connection ID if enabled
        if ($config['fallback_connection_id'] ?? true) {
            return $request->ip() . ':' . getmypid();
        }
        
        return null;
    }
    
    /**
     * Get Redis connection instance
     */
    private function getRedis()
    {
        return $this->redisConnection 
            ? Redis::connection($this->redisConnection)
            : Redis::connection();
    }
    
    /**
     * Get client configuration
     */
    public function getConfig(): array
    {
        return [
            'connection_id' => $this->connectionId,
            'rate_limit_key' => $this->rateLimitKey,
            'max_requests' => $this->maxRequests,
            'window_seconds' => $this->windowSeconds,
            'block_when_exhausted' => $this->blockWhenExhausted,
            'retry_after_seconds' => $this->retryAfterSeconds,
            'redis_connection' => $this->redisConnection,
            'rate_limit_config' => $this->rateLimitConfig
        ];
    }
}