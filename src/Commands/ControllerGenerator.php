<?php

namespace O360Main\SaasBridge\Commands;

use Illuminate\Console\Command as BaseCommand;
use O360Main\SaasBridge\Stubs\CodeGenerate;
use Symfony\Component\Console\Command\Command;

class ControllerGenerator extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *çn
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
     * @return int
     * @throws \Exception
     */
    public function handle(): int
    {
        $x = new CodeGenerate();

        $x->run();

        return Command::SUCCESS;

    }
}
