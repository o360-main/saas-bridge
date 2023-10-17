<?php

namespace O360Main\SaasBridge\Http\Responses;


use Illuminate\Contracts\Support\Responsable;

class TriggerResponse implements Responsable
{

    public function __construct(
        protected readonly bool        $completed,
        protected readonly int|null    $progress_in_percentage = null,
        protected readonly int|null    $interval_in_seconds = null,
        protected readonly bool        $isError = false,
        protected readonly string|null $errorMsg = null,
        protected readonly array       $data = []
    )
    {
        $interval_in_seconds ??= 300;
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
            'completed' => $this->completed,
            'data' => $this->data,
            'interval' => $this->interval_in_seconds,
            'error' => $this->isError,
        ]);
    }

    private function toResponseV2(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'completed' => $this->completed,
            'progress_in_percentage' => $this->progress_in_percentage,
            'interval_in_seconds' => $this->interval_in_seconds,
            'isError' => $this->isError,
            'errorMsg' => $this->errorMsg,
            'data' => $this->data,
        ]);
    }

}
