<?php

namespace O360Main\SaasBridge\Helpers;

final class ResponseHelper
{
    private function __construct()
    {
    }

    public static function json(string $filePath)
    {
        //get json file with exception
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }
        try {
            $json = json_decode(file_get_contents($filePath), true, 512, JSON_THROW_ON_ERROR);
            return response()->json($json);
        } catch (\Exception $e) {
            abort(500, 'Invalid JSON');
        }
    }

}
