<?php

namespace O360Main\SaasBridge\Http\Responses\Manifest;


//"logo": {
//"small": {
//"url": "https://vendhq.saasintegrator.online/logo/logo-sm.svg",
//"type": "svg"
//},
//"medium": {
//    "url": "https://vendhq.saasintegrator.online/logo/logo-md.svg",
//            "type": "svg"
//        },
//        "large": {
//    "url": "https://vendhq.saasintegrator.online/logo/logo-lg.svg",
//            "type": "svg"
//        }

use Illuminate\Contracts\Support\Arrayable;

class ManifestLogo implements Arrayable
{
    public function __construct(
        public readonly Image $small,
        public readonly Image $medium,
        public readonly Image $large,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'small' => $this->small->toArray(),
            'medium' => $this->medium->toArray(),
            'large' => $this->large->toArray(),
        ];
    }
}
