<?php

namespace App\Console\Commands;

use App\Helpers\Apigee\Services\AppService;
use App\Helpers\Apigee\Services\CachesService;
use App\Helpers\Apigee\Services\DeveloperService;
use App\Helpers\Apigee\Services\FlowHookService;
use App\Helpers\Apigee\Services\KVMService;
use App\Helpers\Apigee\Services\ProductService;
use App\Helpers\Apigee\Services\ProxyService;
use App\Helpers\Apigee\Services\ReferencesService;
use App\Helpers\Apigee\Services\SharedFlowService;
use App\Helpers\Apigee\Services\TargetServerService;
use App\Helpers\Apigee\Services\TLSKEyStoreService;
use Illuminate\Console\Command;

class ApigeeCommand extends Command
{
    protected $signature = 'apigee {module} {func?} {additional-parameter?}';

    protected $description = 'Command description';


    public function handle(): void
    {
        app()->singleton('currentCommand', function () {
            return $this;
        });
        $func = $this->argument('func');
        $module = $this->argument('module');
        $param = $this->argument('additional-parameter');

        switch ($module) {

            case'export-all':
                $this->exportAll();
                break;
            case 'proxy':
                $this->Handler($func, ProxyService::class, $param);
                break;
            case 'sharedFlow':
                $this->Handler($func, SharedFlowService::class, $param);
                break;
            case 'cache':
                $this->Handler($func, CachesService::class, $param);
                break;
            case 'flowHook':
                $this->Handler($func, FlowHookService::class, $param);
                break;
            case 'kvm':
                $this->Handler($func, KVMService::class, $param);
                break;
            case 'references':
                $this->Handler($func, ReferencesService::class, $param);
                break;
            case 'targetServer':
                $this->Handler($func, TargetServerService::class, $param);
                break;
            case 'tlsKeyStore':
                $this->Handler($func, TLSKEyStoreService::class, $param);
                break;
            case 'developer':
                $this->Handler($func, DeveloperService::class, $param);
                break;
            case 'app':
                $this->Handler($func, AppService::class, $param);
                break;
            case 'product':
                $this->Handler($func, ProductService::class, $param);
                break;
            default:
                $this->error("Module $module not found");
        }

    }

    private function Handler($func, string $className, $param = null): void
    {
        if (!method_exists($className, $func)) {
            $this->error("Function $func not found");
            return;
        }

        $this->comment("Running $func");
        $object = new $className();
        $output = $object->$func($param);
        if (!empty($output))
            is_string($output) ? $this->comment($output)
                : $this->comment(json_encode($output, JSON_PRETTY_PRINT));
        $this->comment("Done");
    }

    private function exportAll(): void
    {
        $this->comment("Exporting all");

        $this->comment("\n\n\t====================\tExporting Developer Data\t====================   ");
        (new DeveloperService())->exportAll();

        $this->comment("\n\n\t====================\tExporting App Data\t====================   ");
        (new AppService())->exportAll();

        $this->comment("\n\n\t====================\tExporting Product Data\t====================   ");
        (new ProductService())->exportAll();

        $this->comment("\n\n\t====================\tExporting Caches Data\t====================   ");
        (new CachesService())->exportAll();

        $this->comment("\n\n\t====================\tExporting Flowhooks Data\t====================   ");
        (new FlowHookService())->exportAll();

        $this->comment("\n\n\t====================\tExporting KVM Data\t====================   ");
        (new KVMService())->exportAll();

        $this->comment("\n\n\t====================\tExporting References Data\t====================   ");
        (new ReferencesService())->exportAll();

        $this->comment("\n\n\t====================\tExporting Target Server Data\t====================   ");
        (new TargetServerService())->exportAll();

        $this->comment("\n\n\t====================\tExporting TLSKEyStore Data\t====================   ");
        (new TLSKEyStoreService())->exportAll();

        $this->comment("\t====================\tExporting proxies Data\t====================   ");
        (new ProxyService())->exportAll();

        $this->comment("\n\n\t====================\tExporting Shared Flows Data\t====================   ");
        (new SharedFlowService())->exportAll();

        $this->comment("Done");
    }


}
