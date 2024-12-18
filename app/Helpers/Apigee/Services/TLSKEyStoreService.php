<?php

namespace App\Helpers\Apigee\Services;

use App\Helpers\Apigee\Clients\TLSKEyStoreClient;
use App\Helpers\Apigee\Helpers\ConfigEnum;
use App\Helpers\Apigee\Helpers\ServiceAbstract;

class TLSKEyStoreService extends ServiceAbstract
{
    private array $Environments;
    private TLSKEyStoreClient $client;
    const MODULE = 'TLSKEyStore';

    public function __construct(ConfigEnum $configKey = ConfigEnum::ConfigOriginKey)
    {
        parent::__construct();
        $this->Environments = $this->listEnvironments();
        $this->client = new TLSKEyStoreClient($this->config);
    }

    public function listForEnvironment($environmentName)
    {
        return $this->client->list($environmentName);
    }

    public function getForEnvironment($environmentName, $name)
    {
        return $this->client->get($environmentName, $name);
    }

    public function getCertInfo($envName, $name, $certName)
    {
        return $this->client->getCert($envName, $name, $certName);
    }

    public function exportCert($envName, $name, $certName)
    {
        $content= $this->client->exportCert($envName, $name, $certName);
        file_put_contents($this->storage->genericDataPath(self::MODULE, '/certs/', "$certName.pem"),$content);
    }

    public function exportForEnvironment($envName, $includeCertInfo = true)
    {
        $names = $this->listForEnvironment($envName);
        $data = [];
        $certsInfo = [];
        self::commandLog("Saving all " . self::MODULE . " data for environment: $envName");
        foreach ($names as $name) {
            self::commandLog("\t-- Saving " . self::MODULE . ": $name");
            $data[$name] = $this->getForEnvironment($envName, $name);
            if ($includeCertInfo) {
                foreach ($data[$name]['certs'] ?? [] as $certName) {
                    $certsInfo[$certName] = $this->getCertInfo($envName, $name, $certName);
                    $this->exportCert($envName, $name, $certName);
                }
            }
            $data[$name]['certsInfo'] = $certsInfo;
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
