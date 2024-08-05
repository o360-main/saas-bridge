<?php

namespace O360Main\SaasBridge\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class EncryptionCall
{
    public static function decrypt(string $encryptedData, string $key): bool|string
    {
        $decoded = base64_decode($encryptedData);
        $nonce = substr($decoded, 0, 12); // Extract nonce
        $ciphertext = substr($decoded, 12, -16); // Extract ciphertext
        $tag = substr($decoded, -16); // Extract tag
        $key = base64_decode($key);

        return openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag);
    }

    public static function validateJwtToken($token, $key): bool
    {
        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
