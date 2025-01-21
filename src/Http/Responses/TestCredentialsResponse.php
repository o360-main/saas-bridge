<?php

namespace O360Main\SaasBridge\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class TestCredentialsResponse implements Responsable
{
    public function __construct(
        protected bool $success,
        protected string $message,
        protected array $data = []
    ) {}

    public function toResponse($request): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }
}
