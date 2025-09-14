<?php

namespace O360Main\SaasBridge\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use O360Main\SaasBridge\Traits\AttachesProcessingStats;

class TriggerResponse implements Responsable
{
    use AttachesProcessingStats;
    public function __construct(
        protected readonly bool        $is_completed = false,
        protected readonly bool        $is_error = false,
        protected readonly int|null    $progress_in_percentage = null,
        protected readonly int|null    $interval_in_seconds = null,
        protected readonly string|null $error_message = null,
        protected readonly array       $data = [],
        protected readonly bool        $include_processing_in_data = false
    ) {
    }

    public function toResponse($request): \Illuminate\Http\JsonResponse
    {

        return $this->toResponse1_0_0();

        ////        $version = config('saas-bridge.main_version');
        //        $version = $request->header('x-main-version', '1.0.0');
        //        $version = $request->input("_env.version", $version);
        //        return match ($version) {
        //            'v1' => $this->toResponseV1(), // this is for backward compatibility [Will remove soon]
        //            default => $this->toResponse1_0_0(),//now on this is the version 1.0.0
        //        };
        //

    }

    private function toResponseV1(): \Illuminate\Http\JsonResponse
    {
        $responseData = [
            'progress' => $this->progress_in_percentage,
            'completed' => $this->is_completed,
            'data' => $this->data,
            'interval' => $this->interval_in_seconds,
            'error' => $this->is_error,
        ];
        
        // Optionally include processing summary in V1 format
        if ($this->include_processing_in_data) {
            $responseData['processing_summary'] = $this->getProcessingSummary();
        }
        
        $response = response()->json($responseData);
        
        return $this->attachProcessingStats($response);
    }

    private function toResponse1_0_0(): \Illuminate\Http\JsonResponse
    {
        $responseData = [
            'is_completed' => $this->is_completed,
            'progress_in_percentage' => $this->progress_in_percentage,
            'interval_in_seconds' => $this->interval_in_seconds,
            'is_error' => $this->is_error,
            'error_message' => $this->error_message,
            'data' => $this->data,
        ];
        
        // Optionally include processing summary in main response data
        if ($this->include_processing_in_data) {
            $responseData['processing_summary'] = $this->getProcessingSummary();
        }
        
        $response = response()->json($responseData);
        
        return $this->attachProcessingStats($response);
    }

}
