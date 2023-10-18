<?php

namespace O360Main\SaasBridge\Http\Responses\Manifest;

use Illuminate\Contracts\Support\Arrayable;

class ManifestConfig implements Arrayable
{

    public function __construct(
        public readonly int $rate_limit_requests,
        public readonly int $rate_limit_seconds,
        public readonly int $rate_limit_per_day,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'rate_limit_requests' => $this->rate_limit_requests,
            'rate_limit_seconds' => $this->rate_limit_seconds,
            'rate_limit_per_day' => $this->rate_limit_per_day,
        ];
    }


    public static function fromArray(array $data): static
    {
        return new static(
            rate_limit_requests: $data['rate_limit_requests'],
            rate_limit_seconds: $data['rate_limit_seconds'],
            rate_limit_per_day: $data['rate_limit_per_day'],
        );
    }


}
