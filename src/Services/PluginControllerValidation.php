<?php

namespace O360Main\SaasBridge\Services;

use ReflectionClass;

class PluginControllerValidation
{

    /**
     * @throws \ReflectionException
     */
    public static function check(string $controller): void
    {
        $instance = new self();
        $instance->validate($controller);
    }


    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function validate(string $controller): void
    {

        $reflection = new ReflectionClass($controller);

        if (!$reflection->implementsInterface(\O360Main\SaasBridge\Contracts\PluginControllerInterface::class)) {
            throw new \Exception("Controller must implement \O360Main\SaasBridge\Contracts\PluginControllerInterface::class");
        }
    }

}
