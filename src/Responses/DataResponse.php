<?php

namespace O360Main\SaasBridge\Responses;

use Illuminate\Contracts\Support\Responsable;

class DataResponse implements Responsable
{
    public function __construct(
    )
    {
    }

    public function toResponse($request): array
    {
        return [
        ];
    }
}
