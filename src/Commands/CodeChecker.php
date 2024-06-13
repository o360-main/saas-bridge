<?php

namespace O360Main\SaasBridge\Commands;

use Illuminate\Console\Command as BaseCommand;
use O360Main\SaasBridge\Module;
use O360Main\SaasBridge\Services\ControllerValidationService;
use O360Main\SaasBridge\Services\PluginControllerValidation;
use Symfony\Component\Console\Command\Command;

class CodeChecker extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *çn
     * @var string
     */
    protected $signature = 'saas:code-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     *
     * @throws \ReflectionException
     */
    public function handle(): void
    {
        $isError = false;

        $this->info('Checking code...');

        //get all controller nested
        //get all routes wich are using ::module macro

        $modules = collect(Module::cases())
            ->filter(function ($module) {

                $arr = [
//                    Module::seller,
//                    Module::account
                ];

                return !in_array($module, $arr);
            });


        //info checking for missing controller
        $this->info('Checking for missing controller...');


        $controllers = collect(\File::allFiles(app_path('Http/Controllers')))
            ->filter(fn ($controller) => str_ends_with($controller->getRelativePathname(), '.php'))
            ->map(fn ($controller) => $controller->getRelativePathname())
            ->map(fn ($controller) => str_replace('.php', '', $controller))
            ->map(fn ($controller) => str_replace('/', '\\', $controller))
            ->map(fn ($controller) => 'App\\Http\\Controllers\\' . $controller);


        $pluginController = $controllers->filter(fn ($controller) => str_contains($controller, 'Plugin'))->first();

        if (!$pluginController) {
            $this->error('PluginController not found');
        } else {

            try {
                PluginControllerValidation::check($pluginController);
            } catch (\Exception $e) {
                $table[] = [
                    $pluginController,
                    $e->getMessage()
                ];
                $isError = true;
                $this->showTable($table, $isError);
            }

        }


        //first check missing controller
        $missing = $modules
            ->map(fn ($module) => $module->detail('label_plural'))
            ->diff($controllers->map(fn ($controller) => str_replace('Controller', '', class_basename($controller))));


        $table = [];
        foreach ($missing as $item) {
            $isError = true;
            $table[] = [
                $item,
                'Missing Controller'
            ];
        }

        $this->showTable($table, $isError);


        //check missing controller methods
        $this->info('Checking for missing routes declaration...');


        //check missing routes
        $routes = \File::get(base_path('routes/api.php'));


        $table = [];

        foreach ($modules as $module) {
            $route = "Route::module('{$module->plural()}',";
            if (!str_contains($routes, $route)) {
                $isError = true;
                $table[] = [
                    $module->plural(),
                    'Missing Route'
                ];
            }
        }


        $this->showTable($table, $isError);

        //check controller methods
        $this->info('Checking for Valid Controller...');

        $table = [];


        $controllers = $controllers->filter(fn ($c) => !str_contains($c, 'Plugin'));


        foreach ($controllers as $controller) {

            try {
                ControllerValidationService::check($controller);
            } catch (\Exception $e) {

                $isError = true;

                $error = $e->getMessage();

                $table[] = [
                    $controller,
                    $error
                ];

            }

        }

        $this->showTable($table, $isError);

        $this->info('Code check completed');
    }

    private function showTable(array $table, bool &$isError): void
    {
        if (
            $isError) {

            $this->error('Error found');

            $this->table(['Module', 'Error'], $table, 'box-double');

            $this->info('');
            $this->info('');
            $this->info('');

            $isError = false;

        } else {
            $this->info('No error found');
            $this->info('');
        }
    }
}
