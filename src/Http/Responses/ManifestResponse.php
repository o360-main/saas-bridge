<?php

namespace O360Main\SaasBridge\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class ManifestResponse implements Responsable
{


    public static function fromJson($filePath): ?\Illuminate\Http\JsonResponse
    {
        return self::json($filePath);
    }

    public static function json($filePath)
    {
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


    public function toResponse($request): array
    {
        return [];
    }
}
