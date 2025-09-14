<?php

namespace O360Main\SaasBridge\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use O360Main\SaasBridge\Traits\AttachesProcessingStats;

class ImportResponse implements Responsable
{
    use AttachesProcessingStats;
    public function __construct(
        public bool    $success = false,
        public ?string $message = null,
        public array   $data = [],
        public bool    $include_processing_summary = false,
        public bool    $include_processing_details = false,
    ) {
    }

    public function toResponse($request): \Illuminate\Http\JsonResponse
    {
        $responseData = [
            'data' => $this->data,
            'success' => $this->success,
            'message' => $this->message,
        ];
        
        // Include processing summary in response data
        if ($this->include_processing_summary) {
            $responseData['processing_summary'] = $this->getProcessingSummary();
        }
        
        // Include full processing details in response data
        if ($this->include_processing_details) {
            $responseData['processing_details'] = $this->getProcessingDetails();
        }
        
        $response = response()->json($responseData);
        
        return $this->attachProcessingStats($response);
    }
}
