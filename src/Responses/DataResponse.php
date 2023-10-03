<?php

namespace O360Main\SaasBridge\Config;

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
