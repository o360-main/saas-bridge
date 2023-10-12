<?php

function array_deep_default(array $arr, array $defaultArr = []): array
{
    foreach ($defaultArr as $key => $value) {
        if (!isset($arr[$key])) {
            $arr[$key] = $value;
        } else {
            if (is_array($value)) {
                $arr[$key] = array_deep_default($arr[$key], $value);
            }
        }
    }

    return $arr;
}


function path_join(...$path): string
{
    return implode(DIRECTORY_SEPARATOR, $path);
}


function url_make(...$path): string
{
    return implode('/', array_map(fn($arg) => trim($arg, '/'), $path));
}


function array_get(array $arr, $key, $default = null)
{
    return $arr[$key] ?? $default;
}

function array_pull(array &$arr, $key, $default = null)
{
    $value = $arr[$key] ?? $default;
    unset($arr[$key]);
    return $value;
}


function event_action_extract(string $str): string
{
    $arr = explode('.', $str);
    return end($arr);

}



