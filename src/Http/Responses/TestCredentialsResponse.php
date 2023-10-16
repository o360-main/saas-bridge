<?php

namespace O360Main\SaasBridge\Http\Responses;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class TestCredentialsResponse implements Responsable, Arrayable
{

    public function __construct(
        public readonly bool   $success,
        public readonly string $message,
        public readonly array  $data = []
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['success'] ?? false,
            message: $data['message'] ?? '',
            data: $data['data'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }


    public function toResponse($request): JsonResponse
    {
        return response()->json($this->toArray());
    }
}
