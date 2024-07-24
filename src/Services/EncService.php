<?php

namespace O360Main\SaasBridge\Services;

class EncService
{
    protected RSA $rsa;

    protected $basePath = "/";

    //signletone
    private static ?EncService $instance = null;

    public static function getInstance(): EncService
    {
        if (self::$instance === null) {
            self::$instance = new EncService();
        }
        return self::$instance;
    }

    /**
     * @throws \Exception
     */
    private function __construct()
    {
        $pathPrivate = storage_path('private.pem');
        $pathPublic = storage_path('public.pem');

        //check if file exists

        if (!file_exists($pathPrivate) || !file_exists($pathPublic)) {
            throw new \Exception('Keys not found');
        }

        $this->rsa = new RSA(
            file_get_contents($pathPrivate),
            file_get_contents($pathPublic)
        );
    }


    public function sign($data): string
    {
        return $this->rsa->sign($data);
    }

    public function verify($data, $signature): bool
    {
        return $this->rsa->verify($data, $signature);
    }


    public function encrypt($data): string
    {
        return $this->rsa->encrypt($data);
    }

    public function decrypt($data): string
    {
        return $this->rsa->decrypt($data);
    }


    public function publicKey(): string
    {
        //get public key as base_64
        return base64_encode($this->rsa->getPublicKey());
    }


    /**
     * @throws \Exception
     */
    public static function generate(): void
    {
        $pathPrivate = storage_path('private.pem');
        $pathPublic = storage_path('public.pem');

        //check if file exists
        if (file_exists($pathPrivate) || file_exists($pathPublic)) {
            throw new \Exception('Keys already exists');
        }

        $rsa = new RSA();
        $rsa->generateKeys();
        //save to storage folder
        $path = storage_path('/');

        file_put_contents($path . '/private.pem', $rsa->getPrivateKey());
        file_put_contents($path . '/public.pem', $rsa->getPublicKey());

    }


}
