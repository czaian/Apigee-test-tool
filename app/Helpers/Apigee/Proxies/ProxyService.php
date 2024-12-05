<?php

namespace App\Helpers\Apigee\Proxies;

use App\Helpers\Apigee\Clients\ProxyClient;
use App\Helpers\Apigee\Helpers\Config;
use App\Helpers\Apigee\Helpers\ConfigEnum;
use App\Helpers\Apigee\Helpers\ServiceAbstract;
use App\Helpers\Apigee\Helpers\StoragePaths;

class ProxyService extends ServiceAbstract
{
    private ProxyClient $proxyClient;
    private StoragePaths $storage;

    public function __construct(ConfigEnum $configKey = ConfigEnum::ConfigOriginKey)
    {
        $config = new Config($configKey);
        $this->storage = new StoragePaths($config);
        $this->proxyClient = new ProxyClient($config);
    }

    public function listProxies(?int $count = null, ?string $startKey = null, bool $includeRevisions = true, bool $includeMetaData = true)
    {
        return $this->proxyClient->list($count, $startKey, $includeRevisions, $includeMetaData);
    }

    public function get(string $proxyName, ?int $revision = null)
    {
        return $this->proxyClient->get($proxyName, $revision);
    }

    public function exportProxiesData()
    {
        $proxies = $this->listProxies();
        file_put_contents($this->storage->proxiesDataPath(), json_encode($proxies, JSON_PRETTY_PRINT));
        return $proxies;
    }

    public function listDeployments(bool $includeServerStatus = true, bool $includeApiConfig = true)
    {
        return $this->proxyClient->listDeployments($includeServerStatus, $includeApiConfig);
    }

    public function exportProxyDeployments()
    {
        $deployments = $this->listDeployments();
        $deploymentsDataPath = $this->storage->proxyDeploymentsDataPath();
        file_put_contents($deploymentsDataPath, json_encode($deployments, JSON_PRETTY_PRINT));
        return $deployments;
    }


    public function downloadProxyRevision(string $proxyName, int $revisionNumber, $deployedFolder = false): void
    {
        $proxyRevision = $this->proxyClient->download($proxyName, $revisionNumber);
        file_put_contents($this->storage->proxiesFilesPath($proxyName, $revisionNumber, $deployedFolder), $proxyRevision);
    }


    public function downloadAllProxies($allRevisions = false): void
    {
        $proxies = $this->listProxies();
        foreach ($proxies as $proxy) {
            $this->downloadProxyRevision($proxy['name'], last($proxy['revision']));
        }
    }

    public function downloadAllProxiesAllRevisions()
    {
        $proxies = $this->listProxies();
        foreach ($proxies as $proxy) {
            foreach ($proxy['revision'] as $revision)
                $this->downloadProxyRevision($proxy['name'], $revision);
        }
    }

    public function downloadOnlyDeployedProxies()
    {
        $proxiesMap = $this->getDeployedProxiesRevisionMap();
        foreach ($proxiesMap as $proxy) {
            foreach ($proxy['deployed_revisions'] as $revision) {
                self::commandLog('Downloading proxy: ' . $proxy['proxy_name'] . ' revision: ' . $revision);
                $this->downloadProxyRevision($proxy['proxy_name'], $revision, true);
            }
        }
    }

    /**
     * @return array{proxy_name: string,deployed_revisions: array, deployed_revision_cross_env: array}
     *
     */
    public function getDeployedProxiesRevisionMap(): array
    {
        $map = [];

        $deployments = $this->listDeployments();

        foreach ($deployments['environment'] as $environment) {
            $envName = $environment['name'];
            foreach ($environment['aPIProxy'] as $depProxy) {
                $proxyName = $depProxy['name'];
                $map[$proxyName]['proxy_name'] = $proxyName;
                foreach ($depProxy['revision'] as $revision) {
                    $map[$proxyName]['deployed_revisions'][] = $revision['name'];
                    $map[$proxyName]['deployed_revision_cross_env'][$envName][] = $revision['name'];
                }
            }
        }
        return $map;
    }

}
