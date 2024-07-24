<?php

namespace O360Main\SaasBridge\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class DataResponse implements Responsable
{
    public function __construct(
        public array $unique_ids = [],
        public array $meta = [],
    ) {

    }

    public function toResponse($request): array
    {
        return [
            'unique_ids' => $this->unique_ids,
            'meta' => $this->meta,
        ];
    }
}
