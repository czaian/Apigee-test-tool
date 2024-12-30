<?php

namespace App\Helpers\Apigee\Services;

use App\Helpers\Apigee\Helpers\Config;
use App\Helpers\Apigee\Helpers\ConfigEnum;
use App\Helpers\Apigee\Helpers\StoragePaths;

class ReportService
{

    private Config $config;
    private StoragePaths $storage;

    public function __construct(ConfigEnum $configKey = ConfigEnum::ConfigOriginKey)
    {
        $this->config = Config::getInstance($configKey);
        $this->storage = new StoragePaths($this->config);
    }

    public function distributionReport($exportPath = null)
    {
        $exportPath ??= app_path('Helpers/Apigee/ExportedData/DEV-2024-12-17-13-17-39');
        $developers = json_decode(file_get_contents($exportPath . '/Developers/data/developers.json'), 1);
        $apps = json_decode(file_get_contents($exportPath . '/Apps/data/apps.json'), 1);
        //key by name
        $apps = array_combine(array_column($apps, 'name'), $apps);
        $products = json_decode(file_get_contents($exportPath . '/Products/data/products.json'), 1);
        $products = array_combine(array_column($products, 'name'), $products);
        $report = [];
        foreach ($developers as $developer) {
            $report[$developer['email']] = [
                'developer_id' => $developer['developerId'],
                'developer_email' => $developer['email'],
                'developer_name' => $developer['firstName'] . ' ' . $developer['lastName'],
            ];
            //get apps
            foreach ($developer['apps'] as $developerAppName) {
                $appData = $apps[$developerAppName];
                $appRow = [
                    'app_id' => $appData['appId'],
                    'app_name' => $appData['name'],
                    'app_status' => $appData['status'],
                    'created_by' => $appData['createdBy'],
                ];
                //get product
                foreach ($appData['credentials'] as $credential) {
                    $productsArray = $credential['apiProducts'];
                    foreach ($productsArray as $product) {
                        $productName = $product['apiproduct'];
                        $productStatus = $product['status'];
                        $productData = $products[$productName];
                        $appRow['products'][$productName] = [
                            'product_name' => $productData['name'],
                            'display_name' => $productData['displayName'],
                            'product_status' => $productStatus,
                            'environments' => $productData['environments'],
                            'approval_type' => $productData['approvalType'],
                            'api_resources' => $productData['apiResources'],
                            'proxies' => $productData['proxies'],

                        ];
                    }
                }

                $report[$developer['email']]['apps'][$developerAppName] = $appRow;
            }

        }
        $filePath = $exportPath . '/Reports/DevDistributionReport.json';
        $this->storage->validatePath($filePath);
        file_put_contents(
            $filePath,
            json_encode($report, JSON_PRETTY_PRINT)
        );
        return $report;
    }


    public function rowFormat($exportPath = null)
    {
        $exportPath ??= app_path('Helpers/Apigee/ExportedData/DEV-2024-12-17-13-17-39');
        $jsonDevelopers = $this->distributionReport($exportPath);
        $rows = [];
        foreach ($jsonDevelopers as $developer) {
            $developerData = [
                'developer_id' => $developer['developer_id'],
                'developer_email' => $developer['developer_email'],
                'developer_name' => $developer['developer_name'],
            ];
            if (empty(($developer['apps'] ?? []))) {
                $rows[] = $developerData;
                continue;
            }
            foreach ($developer['apps'] as $app) {
                $appData = [
                        'app_id' => $app['app_id'],
                        'app_name' => $app['app_name'],
                        'app_status' => $app['app_status'],
                        'created_by' => $app['created_by'],
                    ] + $developerData;
                if (empty(($app['products'] ?? []))) {
                    $rows[] = $appData;
                    continue;
                }
                foreach ($app['products'] as $product) {
                    $productData = [
                            'product_name' => $product['product_name'],
                            'product_status' => $product['product_status'],
                            'proxies' => implode(",\n", $product['proxies']),
                            'api_resources' => implode(",\n", $product['api_resources']),
                            'environments' => implode(",\n", $product['environments']),
                        ] + $appData;
                    $rows[] = $productData;
                }
            }
        }
        $filePath = $exportPath . '/Reports/DevRowFormat.json';
        $this->storage->validatePath($filePath);
        file_put_contents(
            $filePath,
            json_encode($rows, JSON_PRETTY_PRINT)
        );
        return $rows;
    }

    public function exportAll()
    {
        // TODO: Implement exportAll() method.
    }
}