<?php

namespace App\Console\Commands;

use App\Helpers\Apigee\Services\ReportService;
use App\Helpers\proxiesReport;
use Illuminate\Console\Command;

class proxiesJsonReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apigee:json-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
//        (new proxiesReport($this))->jsonReport();
        (new ReportService())->rowFormat();
    }

}
