<?php

namespace O360Main\SaasBridge\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class ErrorResponse implements Responsable
{
    /**
     * @throws \Exception
     */
    public function __construct(
        protected  int $code,
        protected readonly string $message,
        protected readonly array $data = []
    )
    {
        //validate code
        if ($this->code < 400 || $this->code > 599) {
            throw new \Exception('Invalid error code');
        }

        //throw new \Exception('Invalid error code');
        throw new \Exception($this->message, $this->code);
    }


    public function toResponse($request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'code' => $this->code,
            'message' => $this->message,
            'data' => $this->data,
        ], $this->code);
    }
}
