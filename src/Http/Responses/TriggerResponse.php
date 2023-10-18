<?php

namespace O360Main\SaasBridge\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class TriggerResponse implements Responsable
{
    public function __construct(
        protected readonly bool        $is_completed = false,
        protected readonly bool        $is_error = false,
        protected readonly int|null    $progress_in_percentage = null,
        protected readonly int|null    $interval_in_seconds = null,
        protected readonly string|null $error_message = null,
        protected readonly array       $data = []
    ) {
    }

    public function toResponse($request): \Illuminate\Http\JsonResponse
    {
        $version = config('saas-bridge.main_version');

        return match ($version) {
            'v2' => $this->toResponseV2(),
            default => $this->toResponseV1(),
        };
    }

    private function toResponseV1(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'progress' => $this->progress_in_percentage,
            'completed' => $this->is_completed,
            'data' => $this->data,
            'interval' => $this->interval_in_seconds,
            'error' => $this->is_error,
        ]);
    }

    private function toResponseV2(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'is_completed' => $this->is_completed,
            'progress_in_percentage' => $this->progress_in_percentage,
            'interval_in_seconds' => $this->interval_in_seconds,
            'is_error' => $this->is_error,
            'errorMsg' => $this->error_message,
            'data' => $this->data,
        ]);
    }

}
