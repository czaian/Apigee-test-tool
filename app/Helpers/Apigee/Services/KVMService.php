<?php

namespace App\Helpers\Apigee\Services;

use App\Helpers\Apigee\Clients\KVMClient;
use App\Helpers\Apigee\Helpers\ConfigEnum;
use App\Helpers\Apigee\Helpers\ServiceAbstract;

class KVMService extends ServiceAbstract
{
    private array $Environments;
    private KVMClient $client;
    const MODULE = 'KVM';

    public function __construct(ConfigEnum $configKey = ConfigEnum::ConfigOriginKey)
    {
        parent::__construct();
        $this->Environments = $this->listEnvironments();
        $this->client = new KVMClient($this->config);
    }

    public function listForEnvironment($environmentName)
    {
        return $this->client->list($environmentName);
    }

    public function getForEnvironment($environmentName, $name)
    {
        return $this->client->get($environmentName, $name);
    }

    public function exportForEnvironment($envName)
    {
        $names = $this->listForEnvironment($envName);
        $data = [];
        self::commandLog("Saving all " . self::MODULE . " data for environment: $envName");
        foreach ($names as $cachesName) {
            self::commandLog("\t-- Saving " . self::MODULE . ": $cachesName");
            $data[$cachesName] = $this->getForEnvironment($envName, $cachesName);
        }
        file_put_contents($this->storage->genericDataPath(self::MODULE, $envName), json_encode($data, JSON_PRETTY_PRINT));
        return $data;
    }

    public function exportAll()
    {
        $data = [];
        foreach ($this->Environments as $env) {
            $data[$env] = $this->exportForEnvironment($env);
        }
        file_put_contents($this->storage->genericDataPath(self::MODULE, 'all-environments', "data.json"), json_encode($data, JSON_PRETTY_PRINT));
        return $data;
    }
}
