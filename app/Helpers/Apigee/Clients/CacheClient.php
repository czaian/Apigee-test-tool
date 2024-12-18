<?php

namespace App\Helpers\Apigee\Clients;

use App\Helpers\Apigee\Helpers\Config;
use Illuminate\Support\Facades\Http;

class CacheClient extends ClientAbstract
{
    private string $baseURL;

    public function __construct(private readonly Config $config)
    {
        $this->baseURL = $this->config->baseUrl . '/v1/organizations/' . $this->config->organization;
    }

    public function list(string $environmentName)
    {

        $url = $this->baseURL . '/environments/' . $environmentName . '/caches';
        $response = Http::withBasicAuth($this->config->username, $this->config->password)
            ->get($url);
        return self::handleResponse($response);
    }

    public function get(string $environmentName, $name)

    {
        $url = $this->baseURL . '/environments/' . $environmentName . '/caches/' . $name;

        $response = Http::withBasicAuth($this->config->username, $this->config->password)
            ->get($url);

        return self::handleResponse($response);
    }


}
