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
    protected $signature = 'apigee:json-report {--p|path= : Path to export the report} {--f|folder= : Folder to export the report}';

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

        (new ReportService())->rowFormat($this->option('path'), $this->option('folder'));
        (new ReportService())->jsonReport($this->option('path'), $this->option('folder'));
    }

}
