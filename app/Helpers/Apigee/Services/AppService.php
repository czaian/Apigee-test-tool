<?php

namespace App\Helpers\Apigee\Services;

use App\Helpers\Apigee\Clients\AppClient;
use App\Helpers\Apigee\Clients\DevelopersClient;
use App\Helpers\Apigee\Helpers\ConfigEnum;
use App\Helpers\Apigee\Helpers\ServiceAbstract;

class AppService extends ServiceAbstract
{
    private array $Environments;
    private AppClient $client;
    const MODULE = 'Apps';

    public function __construct(ConfigEnum $configKey = ConfigEnum::ConfigOriginKey)
    {
        parent::__construct();
        $this->Environments = $this->listEnvironments();
        $this->client = new AppClient($this->config);
    }

    public function list($count = 1000, $startKey = null, $expand = true)
    {
        return $this->client->list($count, $startKey, $expand);
    }

    public function get($name)
    {
        return $this->client->get($name);
    }

    public function export()
    {
        $data = $this->list(expand: true);
        $data = $data['app'] ?? $data;

        self::commandLog("Saving all " . self::MODULE . " data");

        file_put_contents($this->storage->genericDataPath(self::MODULE), json_encode($data, JSON_PRETTY_PRINT));
        return $data;
    }

    public function exportAll()
    {
        $data = $this->export();
        return $data;
    }
}
