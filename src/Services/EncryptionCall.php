<?php

namespace O360Main\SaasBridge\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;

class EncryptionCall
{
    public static function decrypt(string $encryptedData, string $key): bool|string
    {
        // check if key is base64 encoded
        if (base64_encode(base64_decode($key, true)) === $key) {
            $key = base64_decode($key);
        }

        //        Log::info('key', [
        //            'key' => $key,
        //        ]);
        //        $key = base64_decode($key);

        $decoded = base64_decode($encryptedData);
        $nonce = substr($decoded, 0, 12); // Extract nonce
        $ciphertext = substr($decoded, 12, -16); // Extract ciphertext
        $tag = substr($decoded, -16); // Extract tag

        return openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag);
    }

    public static function validateJwtToken($token, $key): bool
    {
        try {
            if (base64_encode(base64_decode($key, true)) === $key) {
                $key = base64_decode($key);
            }

            JWT::decode($token, new Key($key, 'HS256'));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
