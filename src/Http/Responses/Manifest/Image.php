<?php

namespace O360Main\SaasBridge\Http\Responses\Manifest;

use Illuminate\Contracts\Support\Arrayable;

class Image implements Arrayable
{
    /**
     * @throws \Exception
     */
    public function __construct(
        public readonly string    $url,
        public readonly ImageType $type,
    ) {
        //        check url is valid
        if (!filter_var($this->url, FILTER_VALIDATE_URL)) {
            throw new \Exception('Invalid URL');
        }

    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'type' => $this->type,
        ];
    }
}
