<?php

namespace O360Main\SaasBridge\Http\Responses;


use Illuminate\Contracts\Support\Responsable;

class TriggerResponse implements Responsable
{

    public function __construct(
        protected readonly bool        $completed,
        protected readonly int|null    $progress_in_percentage = null,
        protected readonly int         $interval_in_seconds = 300,
        protected readonly bool        $isError = false,
        protected readonly string|null $errorMsg = null,
        protected readonly array       $data = []
    )
    {
    }


    public function toResponse($request): \Illuminate\Http\JsonResponse
    {


        $version = config('saas-bridge.main_version');


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
