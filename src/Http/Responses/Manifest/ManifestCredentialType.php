<?php

namespace O360Main\SaasBridge\Http\Responses\Manifest;

enum ManifestCredentialType: string
{
    case TEXT = 'text';
    case PASSWORD = 'password';
    case SELECT = 'select';
    case NUMBER = 'number';
    case URL = 'url';
}
