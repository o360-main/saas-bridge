<?php

namespace O360Main\SaasBridge\Commands;

use Exception;
use Illuminate\Console\Command as BaseCommand;
use O360Main\SaasBridge\Services\EncService;
use Symfony\Component\Console\Command\Command;

class KeyGenerator extends BaseCommand
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'saas:generate:key';

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
     * @throws Exception
     */
    public function handle(): int
    {
        //prompt for ask only stub file
        $this->info('Generating Keys...');


        EncService::generate();

        return Command::SUCCESS;

    }
}
