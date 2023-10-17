<?php

namespace O360Main\SaasBridge\Commands;

use App\Http\Controllers\PluginController;
use Illuminate\Console\Command as BaseCommand;
use Symfony\Component\Console\Command\Command;

class ConfigChecker extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *çn
     * @var string
     */
    protected $signature = 'saas:manifest-test';

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
    public function handle()
    {
        $data = (new PluginController())->manifest();

        $arr = [
            'erp', 'ecom', 'pos'
        ];

        $rules = [
            "type" => "required|in:" . implode(',', $arr),
            'name' => 'required|alpha_dash',//|unique:plugins,name', //Must be a number and length of value is 8
            'label' => 'required|string', //Must be a number and length of value is 8
            'version' => 'required|string',
            'author' => 'string',
            'options.*' => 'array',
            "other" => "array",
//            'options.add' => 'array|required',
//            'options.remove' => 'array',
//            'packages' => 'array',
        ];

        $validate = \Illuminate\Support\Facades\Validator::make($data, $rules);

        if ($validate->fails()) {
            $this->error('Validation failed. Check following errors.');

            //show errors
            //show errros by key value multiple line
            foreach ($validate->errors()->toArray() as $key => $value) {
                $this->error($key . ': ' . implode(', ', $value));
            }

            //check sample plugin repository
            $this->info('Suggest: Check sample plugin repository!!');

            return Command::FAILURE;
        }

        $this->info(':) Validation passed');
    }
}
