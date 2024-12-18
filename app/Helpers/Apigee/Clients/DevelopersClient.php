<?php

namespace App\Helpers\Apigee\Clients;

use App\Helpers\Apigee\Helpers\Config;
use Illuminate\Support\Facades\Http;

class DevelopersClient extends ClientAbstract
{
    private string $baseURL;

    public function __construct(private readonly Config $config)
    {
        $this->baseURL = $this->config->baseUrl . '/v1/organizations/' . $this->config->organization;
    }

    public function list($count = 1000, $startKey = null, $expand = true)
    {

        $url = $this->baseURL . '/developers';
        $response = Http::withBasicAuth($this->config->username, $this->config->password)
            ->withQueryParameters([
                'count' => $count,
                'expand' => $expand? 'true' : 'false'
            ])
            ->when(!empty($startKey), fn($http) => $http->withQueryParameters(['startKey' => $startKey]))
            ->get($url);
        return self::handleResponse($response);
    }

    public function get($name)

    {
        $url = $this->baseURL . '/developers/' . $name;

        $response = Http::withBasicAuth($this->config->username, $this->config->password)
            ->get($url);

        return self::handleResponse($response);
    }


}
