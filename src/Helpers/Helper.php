<?php

namespace O360Main\SaasBridge\Helpers;

class Helper
{
    public static function path(...$path): string
    {
        return implode(DIRECTORY_SEPARATOR, $path);
    }

    public static function url(...$path): string
    {
        return implode('/', array_map(fn($arg) => trim($arg, '/'), $path));
    }

}
