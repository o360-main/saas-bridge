<?php

namespace O360Main\SaasBridge\Http\Responses;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class TriggerResponse implements Responsable, Arrayable
{

    public function __construct(
        public readonly bool        $is_completed = false,
        public readonly int|null    $progress_in_percentage = null,
        public readonly int         $interval_in_seconds = 300,
        public readonly bool        $is_error = false,
        public readonly string|null $error_msg = null,
        public readonly array       $data = []
    )
    {
    }


    public static function fromArray(array $array): static
    {
        return new static(
            is_completed: $array['completed'] ?? false,
            progress_in_percentage: $array['progress_in_percentage'] ?? null,
            interval_in_seconds: $array['interval_in_seconds'] ?? 300,
            is_error: $array['is_error'] ?? false,
            error_msg: $array['error_msg'] ?? null,
            data: $array['data'] ?? [],
        );
    }


    public function toArray(): array
    {

        $version = config('saas-bridge.main_version');

        if ($version === 'v1') {
            return [
                'progress' => $this->progress_in_percentage,
                'completed' => $this->is_completed,
                'data' => $this->data,
                'interval' => $this->interval_in_seconds,
                'error' => $this->is_error,
                'error_msg' => $this->error_msg,
            ];
        }


        return [
            'completed' => $this->is_completed,
            'progress_in_percentage' => $this->progress_in_percentage,
            'interval_in_seconds' => $this->interval_in_seconds,
            'is_error' => $this->is_error,
            'error_msg' => $this->error_msg,
            'data' => $this->data,
        ];
    }


    public function toResponse($request): JsonResponse
    {
        return response()->json($this->toArray());
    }

}
