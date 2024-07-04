<?php

namespace O360Main\SaasBridge\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class EncryptionCall
{
    public static function decrypt(string $encryptedData, string $key): bool|string
    {
        $encryptedData = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($encryptedData, 0, $ivLength);
        $encryptedData = substr($encryptedData, $ivLength);

        return openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);
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
