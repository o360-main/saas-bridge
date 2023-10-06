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


    public static function deepArrDefault(array $arr, array $defaultArr = []): array
    {
        foreach ($defaultArr as $key => $value) {
            if (!isset($arr[$key])) {
                $arr[$key] = $value;
            } else {
                if (is_array($value)) {
                    $arr[$key] = self::deepArrDefault($arr[$key], $value);
                }
            }
        }

        return $arr;
    }
}
