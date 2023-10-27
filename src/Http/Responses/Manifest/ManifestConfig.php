<?php

namespace O360Main\SaasBridge\Http\Responses\Manifest;

use Illuminate\Contracts\Support\Arrayable;

class ManifestConfig implements Arrayable
{
    public function __construct(
        public RateLimiterConfig $rate_limiter_config,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'rate_limiter_config' => $this->rate_limiter_config->toArray(),
        ];
    }


    public static function fromArray(array $data): static
    {
        return new static(
            rate_limiter_config: RateLimiterConfig::fromArray($data['rate_limiter_config']),
        );
    }


}
