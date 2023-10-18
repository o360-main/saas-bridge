<?php

namespace O360Main\SaasBridge\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class ImportResponse implements Responsable
{
    public function __construct(
        public bool    $success = false,
        public ?string $message = null,
        public array   $data = [],
    ) {
    }

    public function toResponse($request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data' => $this->data,
            'success' => $this->success,
            'message' => $this->message,
        ]);
    }
}
