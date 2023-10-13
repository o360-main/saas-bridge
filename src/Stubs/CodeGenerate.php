<?php

namespace O360Main\SaasBridge\Stubs;

use O360Main\SaasBridge\Module;

class CodeGenerate
{
    /**
     * @throws \Exception
     */
    public function run(): void
    {
        //get all modules
        foreach (Module::cases() as $module) {
            $this->generateController($module);
        }

        $this->generateSampleService();

        $this->generateRoutes();
    }


    /**
     * @throws \Exception
     */
    protected function modStub(Module $module): array|bool|string
    {
        $stub = $this->getStub();
        return $this->compileTemplate($stub, $module);
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

            $controller = $module->isSimple() ? 'Simple\\' . $controller : 'Complex\\' . $controller;

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
        $file = "{$controllerName}Controller.php.stub";

        $folder = $module->isSimple() ? '/Simple/' : '/Complex/';


        if (!file_exists(app_path('Http/Controllers/Stubs') . $folder)) {
            mkdir(app_path('Http/Controllers/Stubs') . $folder, 0777, true);
        }

        $path = app_path('Http/Controllers/Stubs') . $folder . $file;


        if (file_exists($path)) {
            //remove
            unlink($path);
//            return;
        }

        file_put_contents($path, $content);
    }


    /**
     * @throws \Exception
     */
    public function generateSampleService(): void
    {

        $files = [
            [
                'file' => 'ModuleService.php.stub',
                'path' => 'app/Services/Stubs/Category/CategoryService.php.stub',
            ],
            [
                'file' => 'ModuleMapper.php.stub',
                'path' => 'app/Services/Stubs/Category/CategoryMapper.php.stub',
            ]
        ];

        foreach ($files as $f) {

            $file = __DIR__ . '/' . $f['file'];
            $path = $f['path'];

            $content = file_get_contents($file);
            $content = $this->compileTemplate($content, Module::category);

            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }

            if (file_exists($path)) {
                unlink($path);
            }

            file_put_contents($path, $content);
        }

    }

    /**
     * @throws \Exception
     */
    private function compileTemplate(string $stub, Module $module)
    {
        $detail = $module->detail();

        $module = [
            'capName' => $detail['label'],
            'smName' => $detail['name'],
            'capPlural' => $detail['label_plural'],
            'smPlural' => $detail['plural'],
        ];

        return str_replace([
            '{{Module}}',
            '{{module}}',
            '{{Modules}}',
            '{{modules}}',
        ], [
            $module['capName'],
            $module['smName'],
            $module['capPlural'],
            $module['smPlural'],
        ], $stub);
    }
}
