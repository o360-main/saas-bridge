<?php

namespace O360Main\SaasBridge\Http\Responses;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;

class ExportResponse implements Responsable, Arrayable
{

    public function __construct(
        public readonly bool    $status = false,
        public readonly ?string $message = null,
        public readonly array   $data = [],
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? false,
            message: $data['message'] ?? null,
            data: $data['data'] ?? [],
        );
    }


    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }


    public function toResponse($request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->toArray());
    }
}
