<?php

namespace App\Console\Commands;

use App\Helpers\Apigee\Proxies\ProxyService;
use Illuminate\Console\Command;

class ApigeeCommand extends Command
{
    protected $signature = 'apigee {module} {func}';

    protected $description = 'Command description';

    public function handle(): void
    {
        app()->singleton('currentCommand', function () {
            return $this;
        });
        $func = $this->argument('func');
        $module = $this->argument('module');

        switch ($module) {
            case 'proxy':
                $this->proxyHandle($func);
                break;
            default:
                $this->error("Module $module not found");
        }

    }

    private function proxyHandle($func)
    {
        if (!method_exists(ProxyService::class, $func)) {
            $this->error("Function $func not found");
            return;
        }

        $this->info("Running $func");
        $object = new ProxyService();
        $object->$func();
        $this->info("Done");
    }


}
