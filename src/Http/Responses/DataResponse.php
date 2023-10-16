<?php

namespace O360Main\SaasBridge\Http\Responses;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class DataResponse implements Responsable, Arrayable
{
    public function __construct()
    {
    }


    public static function fromArray(array $data): self
    {
        return new self();
    }

    public function toArray(): array
    {
        return [
        ];
    }


    public function toResponse($request): JsonResponse
    {
        return response()->json($this->toArray());
    }
}
