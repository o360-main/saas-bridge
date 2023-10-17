<?php

namespace O360Main\SaasBridge\Http\Responses\Manifest;

enum ImageType: string
{
    case SVG = 'svg';
    case PNG = 'png';
    case JPG = 'jpg';
    case JPEG = 'jpeg';
    case GIF = 'gif';
    case WEBP = 'webp';
    case ICO = 'ico';
}
