<?php

namespace O360Main\SaasBridge\Http\Responses\Manifest;

class RateLimiterConfig
{

    public function __construct(
        public readonly ?int $allow_request_per_minute = null,
        public readonly ?int $allow_request_per_day = null,
    )
    {
    }


    public static function fromArray(array $data): static
    {
        return new static(
            allow_request_per_minute: $data['allow_request_per_minute'],
            allow_request_per_day: $data['allow_request_per_day'],
        );
    }

    public function toArray(): array
    {
        return [
            'allow_request_per_minute' => $this->allow_request_per_minute,
            'allow_request_per_day' => $this->allow_request_per_day,
        ];
    }
}
