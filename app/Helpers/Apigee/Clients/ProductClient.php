<?php

namespace App\Helpers\Apigee\Clients;

use App\Helpers\Apigee\Helpers\Config;
use Illuminate\Support\Facades\Http;

class ProductClient extends ClientAbstract
{
    private string $baseURL;

    public function __construct(private readonly Config $config)
    {
        $this->baseURL = $this->config->baseUrl . '/v1/organizations/' . $this->config->organization;
    }

    public function list($count = 1000, $startKey = null, $expand = true, $attributenames = null, $attributevalue = null)
    {

        $url = $this->baseURL . '/apiproducts';
        $response = Http::withBasicAuth($this->config->username, $this->config->password)
            ->withQueryParameters([
                'rows' => $count,
                'expand' => $expand ? 'true' : 'false',
            ])
            ->when(!empty($startKey), fn($http) => $http->withQueryParameters(['startKey' => $startKey]))
            ->when(!empty($attributenames), fn($http) => $http->withQueryParameters(['attributenames' => $attributenames]))
            ->when(!empty($attributevalue), fn($http) => $http->withQueryParameters(['attributevalue' => $attributevalue]))
            ->get($url);
        return self::handleResponse($response);
    }

    public function get($name)

    {
        $url = $this->baseURL . '/apiproducts/' . $name;

        $response = Http::withBasicAuth($this->config->username, $this->config->password)
            ->get($url);

        return self::handleResponse($response);
    }


}
