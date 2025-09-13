<?php

namespace O360Main\SaasBridge\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Redis\Connections\Connection as RedisConnection;
use InvalidArgumentException;

class ConnectionSyncMutex
{
    private string $connectionId;
    private string $lockKey;
    private int $ttl;
    private ?string $lockValue = null;
    private int $retryDelay;
    private int $maxRetries;
    private int $lockTimeout;
    private ?string $redisConnection;

    public function __construct(
        string $lockKey,
        ?string $connectionId = null,
        ?int $ttl = null,
        ?int $retryDelay = null,
        ?int $maxRetries = null,
        ?int $lockTimeout = null
    ) {
        $config = config('saas-bridge.mutex', []);
        
        $keyPrefix = $config['key_prefix'] ?? 'saas_mutex';
        $this->lockKey = $keyPrefix . ":" . $lockKey;
        $this->connectionId = $connectionId ?? $this->getConnectionIdFromRequest();
        $this->ttl = $ttl ?? $config['ttl'] ?? 30;
        $this->retryDelay = $retryDelay ?? $config['retry_delay'] ?? 100; // milliseconds
        $this->maxRetries = $maxRetries ?? $config['max_retries'] ?? 100;
        $this->lockTimeout = $lockTimeout ?? $config['lock_timeout'] ?? 30; // seconds
        $this->redisConnection = $config['redis_connection'] ?? null;
        
        if (empty($this->connectionId)) {
            throw new InvalidArgumentException('Connection ID cannot be empty');
        }
    }

    /**
     * Create a new lock instance with connection ID from request header
     */
    public static function make(string $lockKey, ?Request $request = null): self
    {
        $connectionId = null;
        $config = config('saas-bridge.mutex', []);
        $headers = $config['connection_headers'] ?? ['X-Connection-ID', 'Connection-ID', 'connection-id'];
        
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
        
        return new self($lockKey, $connectionId);
    }

    /**
     * Acquire the lock (blocking with timeout)
     * Similar to Go's mutex.Lock()
     */
    public function lock(?int $timeoutSeconds = null): bool
    {
        $timeout = $timeoutSeconds ?? $this->lockTimeout;
        $startTime = time();
        $maxTime = $startTime + $timeout;
        
        $attempts = 0;
        while (time() < $maxTime && $attempts < $this->maxRetries) {
            if ($this->tryLock()) {
                return true;
            }
            
            $attempts++;
            
            // Sleep for retry delay (convert milliseconds to microseconds)
            usleep($this->retryDelay * 1000);
        }
        
        $elapsed = time() - $startTime;
        throw new \RuntimeException(
            "Failed to acquire lock '{$this->lockKey}' after {$elapsed}s (timeout: {$timeout}s, attempts: {$attempts})"
        );
    }

    /**
     * Try to acquire the lock (non-blocking)
     * Similar to Go's mutex.TryLock()
     */
    public function tryLock(): bool
    {
        $this->lockValue = $this->generateLockValue();
        
        // Use Redis SET with NX (only if not exists) and EX (expiration)
        $result = $this->redis()->set(
            $this->lockKey,
            $this->lockValue,
            'EX',
            $this->ttl,
            'NX'
        );
        
        return $result === 'OK';
    }

    /**
     * Release the lock
     * Similar to Go's mutex.Unlock()
     */
    public function unlock(): bool
    {
        if (!$this->lockValue) {
            return false;
        }
        
        // Use Lua script to ensure atomic check-and-delete
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';
        
        $result = $this->redis()->eval($script, 1, $this->lockKey, $this->lockValue);
        
        if ($result) {
            $this->lockValue = null;
            return true;
        }
        
        return false;
    }

    /**
     * Execute a callback with the lock acquired
     * Similar to Go's sync.Mutex usage pattern
     */
    public function synchronized(callable $callback, ?int $timeoutSeconds = null)
    {
        $this->lock($timeoutSeconds);
        
        try {
            return $callback();
        } finally {
            $this->unlock();
        }
    }

    /**
     * Check if the lock is currently held by this instance
     */
    public function isLocked(): bool
    {
        if (!$this->lockValue) {
            return false;
        }
        
        return $this->redis()->get($this->lockKey) === $this->lockValue;
    }

    /**
     * Extend the lock TTL
     */
    public function extend(int $additionalTtl = null): bool
    {
        if (!$this->lockValue || !$this->isLocked()) {
            return false;
        }
        
        $newTtl = $additionalTtl ?? $this->ttl;
        
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("EXPIRE", KEYS[1], ARGV[2])
            else
                return 0
            end
        ';
        
        return $this->redis()->eval($script, 1, $this->lockKey, $this->lockValue, $newTtl) === 1;
    }

    /**
     * Get lock information
     */
    public function getLockInfo(): array
    {
        return [
            'key' => $this->lockKey,
            'connection_id' => $this->connectionId,
            'ttl' => $this->ttl,
            'is_locked' => $this->isLocked(),
            'lock_value' => $this->lockValue,
        ];
    }

    /**
     * Generate a unique lock value
     */
    private function generateLockValue(): string
    {
        return $this->connectionId . ':' . uniqid() . ':' . microtime(true);
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
        $config = config('saas-bridge.mutex', []);
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
    private function redis(): RedisConnection
    {
        return $this->redisConnection 
            ? Redis::connection($this->redisConnection)
            : Redis::connection();
    }

    /**
     * Destructor to ensure lock is released
     */
    public function __destruct()
    {
        if ($this->lockValue) {
            $this->unlock();
        }
    }
}