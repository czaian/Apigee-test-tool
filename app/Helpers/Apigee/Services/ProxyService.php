<?php

namespace App\Helpers\Apigee\Services;

use App\Helpers\Apigee\Clients\ProxyClient;
use App\Helpers\Apigee\Helpers\ConfigEnum;
use App\Helpers\Apigee\Helpers\ServiceAbstract;

class ProxyService extends ServiceAbstract
{
    protected ProxyClient $proxyClient;

    public function __construct(ConfigEnum $configKey = ConfigEnum::ConfigOriginKey)
    {
        parent::__construct(ConfigEnum::ConfigOriginKey);
        $this->proxyClient = new ProxyClient($this->config);
    }

    public function list(?int $count = null, ?string $startKey = null, bool $includeRevisions = true, bool $includeMetaData = true)
    {
        return $this->proxyClient->list($count, $startKey, $includeRevisions, $includeMetaData);
    }

    public function get(string $proxyName, ?int $revision = null)
    {
        return $this->proxyClient->get($proxyName, $revision);
    }

    public function exportData()
    {
        $proxies = $this->list();
        self::commandLog('Saving all proxies data', 'comment');
        file_put_contents($this->storage->proxiesDataPath(), json_encode($proxies, JSON_PRETTY_PRINT));
        return $proxies;
    }

    public function listDeployments(bool $includeServerStatus = true, bool $includeApiConfig = true)
    {
        return $this->proxyClient->listDeployments($includeServerStatus, $includeApiConfig);
    }

    public function exportDeployments()
    {
        $deployments = $this->listDeployments();
        $deploymentsDataPath = $this->storage->proxyDeploymentsDataPath();
        self::commandLog('Saving all deployments data', 'comment');
        file_put_contents($deploymentsDataPath, json_encode($deployments, JSON_PRETTY_PRINT));
        return $deployments;
    }


    public function downloadRevision(string $proxyName, int $revisionNumber, $deployedFolder = false): void
    {
        $proxyRevision = $this->proxyClient->download($proxyName, $revisionNumber);
        self::commandLog("\t -- Saving Proxy: $proxyName Revision: $revisionNumber");
        file_put_contents($this->storage->proxiesFilesPath($proxyName, $revisionNumber, $deployedFolder), $proxyRevision);
    }


    public function downloadAll($allRevisions = true): void
    {
        $proxies = $this->list();
        self::commandLog('Downloading all proxies', 'comment');
        foreach ($proxies as $proxy) {
            $revisions = $proxy['revision'];
            $revision = array_pop($revisions);
            do {
                $this->downloadRevision($proxy['name'], $revision);
                $revision = array_pop($revisions);
            } while ($allRevisions && $revision);
        }
    }

    public function downloadLastRevision(): void
    {
        $proxies = $this->list();
        self::commandLog('Downloading all proxies', 'comment');
        foreach ($proxies as $proxy) {
            $revision = last($proxy['revision']);
                $this->downloadRevision($proxy['name'], $revision);
        }
    }

    public function downloadOnlyDeployedRevisions()
    {
        $proxiesMap = $this->getDeployedRevisionMap();
        self::commandLog('Downloading only deployed revisions', 'comment');
        foreach ($proxiesMap as $proxy) {
            foreach ($proxy['deployed_revisions'] as $revision) {
                $this->downloadRevision($proxy['name'], $revision, true);
            }
        }
    }

    /**
     * @return array{proxy_name: string,deployed_revisions: array, deployed_revision_cross_env: array}
     *
     */
    public function getDeployedRevisionMap(): array
    {
        $map = [];

        $deployments = $this->listDeployments();

        foreach ($deployments['environment'] as $environment) {
            $envName = $environment['name'];
            foreach ($environment['aPIProxy'] as $depProxy) {
                $proxyName = $depProxy['name'];
                $map[$proxyName]['name'] = $proxyName;
                foreach ($depProxy['revision'] as $revision) {
                    $map[$proxyName]['deployed_revisions'][] = $revision['name'];
                    $map[$proxyName]['deployed_revision_cross_env'][$envName][] = $revision['name'];
                }
            }
        }
        self::commandLog('Saving Proxy-Deployment-Map', 'comment');
        file_put_contents($this->storage->proxiesDataPath('Proxy-Deployment-Map.json'), json_encode($map, JSON_PRETTY_PRINT));
        return $map;
    }


    public function exportAll()
    {
        $this->exportDeployments();
        $this->exportData();
        $this->downloadAll();
        $this->downloadOnlyDeployedRevisions();
    }

}
