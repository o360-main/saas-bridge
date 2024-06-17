<?php

namespace O360Main\SaasBridge\Commands;

use Illuminate\Console\Command as BaseCommand;
use O360Main\SaasBridge\Services\EncService;
use O360Main\SaasBridge\Services\RSA;
use O360Main\SaasBridge\Services\SignatureService;
use O360Main\SaasBridge\Stubs\CodeGenerate;
use Symfony\Component\Console\Command\Command;

class SignatureGenerator extends BaseCommand
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'saas:generate:sign';

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
        //prompt for ask only stub file


        $encService = EncService::getInstance();


        //show public key as private
        $this->info('Public Key: ');
        $this->info('------------------------------------');
        $this->info($encService->publicKey());
        $this->info('------------------------------------');


        $this->info('Generating Signature...');
        $this->info('Signature: Copy this signature and paste it in the plugin settings');
        $this->info("------------------------------------");
        $this->info((new SignatureService())->generateSignature());
        $this->info("------------------------------------");
        return Command::SUCCESS;

    }
}
