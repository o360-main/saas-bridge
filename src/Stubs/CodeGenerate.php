<?php

namespace O360Main\SaasBridge\Stubs;

use O360Main\SaasBridge\Module;

class CodeGenerate
{
    /**
     * @throws \Exception
     */
    public function run()
    {
        //get all modules
        foreach (Module::cases() as $module) {
            $this->generateController($module);
        }

        $this->generateRoutes();
    }


    /**
     * @throws \Exception
     */
    protected function modStub(Module $module): array|bool|string
    {

        $stub = $this->getStub();

        $detail = $module->detail();

        $module = [
            'capName' => $detail['label'],
            'smName' => $detail['name'],
            'capPlural' => $detail['label_plural'],
            'smPlural' => $detail['plural'],
        ];

        $stub = str_replace('{{Module}}', $module['capName'], $stub);
        $stub = str_replace('{{module}}', $module['smName'], $stub);
        $stub = str_replace('{{Modules}}', $module['capPlural'], $stub);
        return str_replace('{{modules}}', $module['smPlural'], $stub);

    }


    protected function getStub(): bool|string
    {
        $file = __DIR__ . '/ModuleController.php.stub';
        return file_get_contents($file);
    }

    /**
     * @throws \Exception
     */
    private function generateRoutes(): void
    {
        $routeFile = base_path('routes/api.php');

        $routes = '//--SaasBridge--//' . PHP_EOL;

        foreach (Module::cases() as $module) {

            $plural = $module->detail('plural');
            $controller = $module->detail('label_plural') . 'Controller';

            $routes .= <<<PHP
Route::module('{$plural}',\App\Http\Controllers\\{$controller}Controller::class);\n
PHP;
        }

        $content = file_get_contents($routeFile);

        $content = str_replace('//--SaasBridge--//', $routes, $content);
        //append
        file_put_contents($routeFile, $content);

    }

    /**
     * @throws \Exception
     */
    private function generateController(Module $module): void
    {
        $controllerName = $module->detail('label_plural');

        $content = $this->modStub($module);
        $file = "{$controllerName}Controller.php";

        $folder = $module->isSimple() ? '/Simple/' : '/Complex/';

        $path = app_path('Http/Controllers') . $folder . $file;

        if (file_exists($path)) {
            return;
        }

        file_put_contents($path, $content);
    }
}
