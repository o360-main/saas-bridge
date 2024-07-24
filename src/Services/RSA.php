<?php

namespace O360Main\SaasBridge\Services;

class RSA
{
    private mixed $privateKey;
    private mixed $publicKey;

    public function __construct($privateKey = null, $publicKey = null)
    {
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
    }

    public function generateKeys($keySize = 2048): void
    {
        $res = openssl_pkey_new([
            "private_key_bits" => $keySize,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($res, $this->privateKey);
        $keyDetails = openssl_pkey_get_details($res);
        $this->publicKey = $keyDetails['key'];
    }

    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    public function getPublicKey()
    {
        return $this->publicKey;
    }

    public function sign($data): string
    {
        openssl_sign($data, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    public function verify($data, $signature): bool
    {
        $decodedSignature = base64_decode($signature);
        return openssl_verify($data, $decodedSignature, $this->publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    public function encrypt($data): string
    {
        openssl_public_encrypt($data, $encrypted, $this->publicKey);
        return base64_encode($encrypted);
    }

    public function decrypt($data): string
    {
        $decodedData = base64_decode($data);
        openssl_private_decrypt($decodedData, $decrypted, $this->privateKey);
        return $decrypted;
    }


    public static function test(): void
    {
        // Usage example
        $rsa = new RSA();
        $rsa->generateKeys();

        $privateKey = $rsa->getPrivateKey();
        $publicKey = $rsa->getPublicKey();

        echo "Private Key:\n$privateKey\n";
        echo "Public Key:\n$publicKey\n";

        $data = "Hello, World!";
        $signature = $rsa->sign($data);
        echo "Signature: $signature\n";

        $isVerified = $rsa->verify($data, $signature) ? 'true' : 'false';
        echo "Signature verified: $isVerified\n";

        $encrypted = $rsa->encrypt($data);
        echo "Encrypted: $encrypted\n";

        $decrypted = $rsa->decrypt($encrypted);
        echo "Decrypted: $decrypted\n";
    }
}
