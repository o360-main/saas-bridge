<?php

namespace O360Main\SaasBridge\Stubs;

use Illuminate\Support\Str;
use O360Main\SaasBridge\Module;

class CodeGenerate
{
    public function __construct(
        protected bool $stub = false,
        protected bool $routes = false,
    ) {
    }


    /**
     * @throws \Exception
     */
    public function run()
    {

        //get all modules
        foreach (Module::cases() as $module) {
            $this->generateController($module);
        }


        $this->generateSampleService();


        if ($this->routes) {
            $this->generateRoutes();

        }
    }


    /**
     * @throws \Exception
     */
    protected function modStub(Module $module): array|bool|string
    {

        $stub = $this->getStub();

        $detail = $module->detail();

        $stub = str_replace('{{type}}', $module->isSimple() ? 'Simple' : 'Complex', $stub);

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

        $content = file_get_contents($routeFile);

        $routes = '//--SaasBridge--//' . PHP_EOL;

        foreach (Module::cases() as $module) {

            $plural = $module->detail('plural');
            $controller = $module->detail('label_plural') . 'Controller';

            $folder = $module->isSimple() ? 'Simple\\' : 'Complex\\';

            $controller = $folder . $controller;

            $_ro = <<<PHP
Route::module('{$plural}',\App\Http\Controllers\\{$controller}Controller::class);\n
PHP;

            if (!Str::contains($content, $_ro)) {
                $routes .= "//".$_ro;// ->make commented routes
            }

        }


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

        //        $file = match ($this->stub)
        //        {
        //            true => "{$controllerName}Controller.php.stub",
        //            default => "{$controllerName}Controller.php",
        //        };

        $file = "{$controllerName}Controller.php.stub";

        $folder = $module->isSimple() ? '/Simple/' : '/Complex/';

        $folder = app_path('Http/Controllers') . $folder;


        //        mkdir($folder, recursive: true);
        if (!file_exists($folder)) {
            mkdir($folder, recursive: true);
        }

        $path =  $folder . $file;

        //if file exists then overwrite

        if (file_exists($path)) {
            unlink($path);
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
                'path' => 'app/Services/Simple/Category/CategoryService.php.stub',
            ],
            [
                'file' => 'ModuleMapper.php.stub',
                'path' => 'app/Services/Modules/Simple/Category/CategoryMapper.php.stub',
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
