<?php

namespace O360Main\SaasBridge\Services;

use ReflectionClass;

class ControllerValidationService
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
    public function validate($controller): void
    {

        $strictMode = config('saas-bridge.strict_mode');

        if (!$strictMode) {
            return;
        }


        $reflection = new ReflectionClass($controller);

        if (!$reflection->implementsInterface(\O360Main\SaasBridge\Contracts\ControllerInterface::class)) {
            throw new \Exception("{$controller} must implement ControllerInterface");
        }


        $functions = [
            'data' => \O360Main\SaasBridge\Http\Requests\DataRequest::class,
            'config' => \O360Main\SaasBridge\Http\Requests\ConfigRequest::class,
            'import' => \O360Main\SaasBridge\Http\Requests\ImportRequest::class,
            'export' => \O360Main\SaasBridge\Http\Requests\ExportRequest::class,
            'trigger' => \O360Main\SaasBridge\Http\Requests\TriggerRequest::class,
        ];


        foreach ($functions as $fn => $req) {
            //check method argument is DataRequest
            $reflection = new \ReflectionMethod($controller, $fn);

            if ($reflection->getParameters()[0]->getType()->getName() !== $req) {
                throw new \Exception("{$controller} :-> {$fn} method argument must be {$req}");
            }
        }
    }

}
