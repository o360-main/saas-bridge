<?php

namespace O360Main\SaasBridge\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class DataResponse implements Responsable
{
    public function __construct(
        protected $unique_ids = [],
    )
    {

    }

    public function toResponse($request): array
    {
        return [
            'unique_ids' => $this->unique_ids,
        ];
    }
}
