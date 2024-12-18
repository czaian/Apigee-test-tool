<?php

namespace App\Helpers\Apigee\Services;

use App\Helpers\Apigee\Clients\SharedFlowClient;
use App\Helpers\Apigee\Helpers\ConfigEnum;
use App\Helpers\Apigee\Helpers\ServiceAbstract;

class SharedFlowService extends ServiceAbstract
{

    private SharedFlowClient $client;

    public function __construct(ConfigEnum $configKey = ConfigEnum::ConfigOriginKey)
    {
        parent::__construct($configKey);
        $this->client = new SharedFlowClient($this->config);
    }

    public function list(?int $count = null, ?string $startKey = null, bool $includeMetaData = true)
    {
        return $this->client->list($count, $startKey, $includeMetaData);
    }

    public function get(string $sharedFlowName, ?int $revision = null)
    {
        return $this->client->get($sharedFlowName, $revision);
    }

    public function exportData()
    {
        $sharedFlows = $this->list();
        self::commandLog('Saving all shared flows data','comment');
        file_put_contents($this->storage->sharedFlowsDataPath(), json_encode($sharedFlows, JSON_PRETTY_PRINT));
        return $sharedFlows;
    }

    public function listDeployments(bool $includeServerStatus = true, bool $includeApiConfig = true)
    {
        return $this->client->listDeployments($includeServerStatus, $includeApiConfig);
    }

    public function exportDeployments(): void
    {
        $deployments = $this->listDeployments();
        $deploymentsDataPath = $this->storage->sharedFlowDeploymentsDataPath();
        self::commandLog('Saving all deployments data','comment');
        file_put_contents($deploymentsDataPath, json_encode($deployments, JSON_PRETTY_PRINT));
    }

    public function downloadRevision(string $sharedFlowName, int $revisionNumber, $deployedFolder = false): void
    {
        $sharedFlowRevision = $this->client->download($sharedFlowName, $revisionNumber);
        self::commandLog("\t-- Saving Shared Flow: $sharedFlowName Revision: $revisionNumber");
        file_put_contents($this->storage->sharedFlowsFilesPath($sharedFlowName, $revisionNumber, $deployedFolder), $sharedFlowRevision);
    }

    public function downloadAll($allRevisions = true): void
    {
        self::commandLog('Downloading all shared flows','comment');
        $sharedFlows = $this->list();
        foreach ($sharedFlows as $sharedFlow) {
            $revisions = $sharedFlow['revision'];
            $revision = array_pop($revisions);
            do {
                $this->downloadRevision($sharedFlow['name'], $revision);
                $revision = array_pop($revisions);
            } while ($allRevisions && $revision);
        }
    }

    public function downloadOnlyDeployedRevisions()
    {
        $map = $this->getDeployedRevisionMap();
        self::commandLog('Downloading only deployed revisions','comment');
        foreach ($map as $item) {
            foreach ($item['deployed_revisions'] as $revision) {
                $this->downloadRevision($item['name'], $revision, true);
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
        self::commandLog('Saving shared flows deployment map','comment');
        file_put_contents($this->storage->sharedFlowsDataPath('sharedFlows-Deployment-Map.json'), json_encode($map, JSON_PRETTY_PRINT));
        return $map;
    }

    public function exportAll(): void
    {
        $this->exportData();
        $this->exportDeployments();
        $this->downloadAll();
        $this->downloadOnlyDeployedRevisions();
    }

}
