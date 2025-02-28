<?php

namespace O360Main\SaasBridge\Commands;

use Illuminate\Console\Command as BaseCommand;
use O360Main\SaasBridge\Stubs\CodeGenerate;
use Symfony\Component\Console\Command\Command;

class ControllerGenerator extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saas:generate:controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle(): int
    {
        // prompt for ask only stub file
        $this->info('Generating Controller...');

        //        $ask = $this->ask('Do you want to generate only stub file? (y/n)');
        //
        //        $stub = false;
        //        if ($ask === 'y') {
        //            $stub = true;
        //        }

        $routes = false;
        $ask = $this->ask('Do you want to generate routes? (y/n)');
        if ($ask === 'y') {
            $this->info('Generating routes...');
            $routes = true;
        } else {
            $this->info('Skipping routes generation...');
        }

        $x = new CodeGenerate(
            stub: true,
            routes: $routes
        );

        $x->run();

        return Command::SUCCESS;

    }
}
