<?php

namespace App\Helpers\Apigee\Helpers;

readonly class StoragePaths
{

    public function __construct(private Config $config)
    {
    }

    private function validatePath(string $path = ''): void
    {
        $dirPath = dirname($path);
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0777, true);
        }
    }

    public function proxiesFilesPath(string $proxyName = null, $proxyRevision = null, bool $deployedFolder = false): string
    {
        $folderPath = $this->exportedDataPath('proxies/files');
        if (empty($proxyName)) return $folderPath;

        $proxyBundleName = $proxyName . '_revision_' . ($proxyRevision ?? '') . '.zip';

        $fullPath = $folderPath . '/' . ($deployedFolder ? "deployed_proxies/$proxyName" : $proxyName);

        $this->validatePath($fullPath . '/' . $proxyBundleName);
        return $fullPath . '/' . $proxyBundleName;
    }

    public function proxiesDataPath($fileName = 'proxies.json'): string
    {
        $path = $this->exportedDataPath('proxies/data/' . $fileName);
        $this->validatePath($path);
        return $path;
    }

    public function proxyDeploymentsDataPath(): string
    {
        $path = $this->exportedDataPath('proxies/data/deployments.json');
        $this->validatePath($path);
        return $path;
    }

    private function exportedDataPath(string $path = ''): string
    {
        $basePath = $this->config->exportFolder;
        $fullPath = empty($path) ? $basePath : $basePath . '/' . $path;
        return $fullPath;
    }


    public function sharedFlowsFilesPath(string $sharedFlowName = null, $sharedFlowRevision = null, bool $deployedFolder = false): string
    {
        $folderPath = $this->exportedDataPath('sharedFlows/files');
        if (empty($sharedFlowName)) return $folderPath;

        $sharedFlowBundleName = $sharedFlowName . '_revision_' . ($sharedFlowRevision ?? '') . '.zip';

        $fullPath = $folderPath . '/' . ($deployedFolder ? "deployed_sharedFlows/$sharedFlowName" : $sharedFlowName);

        $this->validatePath($fullPath . '/' . $sharedFlowBundleName);
        return $fullPath . '/' . $sharedFlowBundleName;
    }

    public function sharedFlowsDataPath($fileName = 'sharedFlows.json'): string
    {
        $path = $this->exportedDataPath('sharedFlows/data/' . $fileName);
        $this->validatePath($path);
        return $path;
    }

    public function sharedFlowDeploymentsDataPath(): string
    {
        $path = $this->exportedDataPath('sharedFlows/data/deployments.json');
        $this->validatePath($path);
        return $path;
    }


    public function cacheDataPath($envName, $fileName = 'caches.json'): string
    {
        $path = $this->exportedDataPath("Caches/data/$envName-$fileName");
        $this->validatePath($path);
        return $path;
    }


    public function FlowHookDataPath($envName, $fileName = 'flowhook.json'): string
    {
        $path = $this->exportedDataPath("Flowhooks/data/$envName-$fileName");
        $this->validatePath($path);
        return $path;
    }

    public function KVMDataPath($envName, $fileName = 'KVM.json'): string
    {
        $path = $this->exportedDataPath("KVM/data/$envName-$fileName");
        $this->validatePath($path);
        return $path;
    }

    public function genericDataPath($module, $envName = null, $fileName = null): string
    {
        $fileName ??= "$module.json";
        $envName = is_null($envName) ? '' : $envName . '-';
        $path = $this->exportedDataPath("$module/data/$envName$fileName");
        $this->validatePath($path);
        return $path;
    }

}
