<?php

namespace O360Main\SaasBridge\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class PingResponse implements Responsable
{

    public function __construct(
        protected bool   $success,
        protected string $message,
        protected array  $data = []
    )
    {
    }


    public function toResponse($request)
    {
        // TODO: Implement toResponse() method.
    }
}
