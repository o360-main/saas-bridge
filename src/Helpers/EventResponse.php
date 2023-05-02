<?php

namespace O360Main\SaasBridge\Helpers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class EventResponse implements Responsable
{

    private array $data = [];
    private bool $status;
    private string $message;


    public static function make(): self
    {
        return new self();
    }


    //setters
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function toResponse($request): array
    {
        return [
            'data' => $this->data,
            'status' => $this->status,
            'message' => $this->message,
        ];
    }


    public static function import(mixed $data, bool $status, string|null $message = null): JsonResponse
    {
        return response()->json(compact('data', 'status', 'message'));
    }

    public static function export(mixed $data, bool $status, string|null $message = null): JsonResponse
    {
        return response()->json(compact('data', 'status', 'message'));
    }

}
