<?php

namespace App\Helpers;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class proxiesReport extends Command
{
    public function __construct(private readonly Command $command)
    {

    }

    public function jsonReport(): void
    {
        $folderEnv = 'prod';
        $storagePath = storage_path("app/private/$folderEnv");
        $this->command->info('start preparing data');
        $proxiesList = collect(json_decode(file_get_contents($storagePath . "/proxies.json"), 1));
        $deployments = json_decode(file_get_contents($storagePath . "/proxyDeployments.json"), 1);
        $deployments = collect($deployments['environment']);
        $envs = $deployments->pluck('name');

        $this->command->info(count($envs) . ' environments found, (' . implode(",", $envs->toArray()) . ")");
        //prepare proxy list
        $proxiesListArr = $proxiesList
            ->keyBy('name')
            ->map(function ($proxy) use ($envs) {
                $proxy = array_merge($proxy, $proxy['metaData']);
                foreach ($envs as $env)
                    $proxy[$env . " Deployed Revisions"] = '';
                unset($proxy['metaData']);
                $proxy['revision'] = implode(',', $proxy['revision']);
                return $proxy;
            })->sortByDesc('lastModifiedAt')->toArray();
        $this->command->info(count($proxiesListArr) . ' proxies found');
        //prepare deployments
        $deployments = $deployments->map(function ($deployment) use (&$proxiesListArr) {
            //map environments
            $env = $deployment['name'];
            foreach ($deployment['aPIProxy'] as $proxiesDeployment) {
                $revisions = Arr::pluck($proxiesDeployment['revision'], 'name');
                $proxiesListArr[$proxiesDeployment['name']][$env . " Deployed Revisions"] = implode(",", $revisions);
            }
        });
        $this->command->info('Data prepared successfully as json');
        $filePath = $storagePath . '/proxiesJsonReport.json';
        $content = json_encode(array_values($proxiesListArr), JSON_PRETTY_PRINT);
        file_put_contents($filePath, $content);
        $this->command->info('Data saved to ' . $filePath);

    }


}
