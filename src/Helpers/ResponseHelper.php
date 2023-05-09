<?php

namespace O360Main\SaasBridge\Helpers;

use Illuminate\Http\JsonResponse;

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


    public static function trigger(
        bool        $completed,
        int|null    $progressPercentage = null,
        int         $interval = 300,
        bool        $isError = false,
        string|null $errorMsg = null,
        array       $data = []
    ): JsonResponse
    {

        if ($progressPercentage > 100) {
            $progressPercentage = 100;
        }

        return response()->json([
            'progress' => $progressPercentage,
            'completed' => $completed,
            'data' => $data,
            'interval' => $interval,
            'error' => $isError,
        ]);
    }

    public static function import(mixed $data, bool $status, string $message = null): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'status' => $status,
            'message' => $message,
        ]);
    }

    public static function export(mixed $data, bool $status, string $message = null): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'status' => $status,
            'message' => $message,
        ]);
    }

    public static function ok(mixed $data, bool $status, string $message = null): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'status' => $status,
            'message' => $message,
        ]);
    }

}
