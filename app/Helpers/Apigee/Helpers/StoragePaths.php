<?php

namespace App\Helpers\Apigee\Helpers;

readonly class StoragePaths
{

    public function __construct(private Config $config)
    {
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

    public function proxiesDataPath(): string
    {
        $path = $this->exportedDataPath('proxies/data/proxies.json');
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

    private function validatePath(string $path = ''): void
    {
        $dirPath = dirname($path);
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0777, true);
        }
    }

}
