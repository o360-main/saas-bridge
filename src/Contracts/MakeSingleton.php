<?php

namespace O360Main\SaasBridge\Contracts;

trait MakeSingleton
{
    /**
     * set type of class
     * @var self
     */
    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
