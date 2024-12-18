<?php

namespace App\Helpers\Apigee\Clients;

use App\Helpers\Apigee\Helpers\Config;
use Illuminate\Support\Facades\Http;

class EnvironmentClient extends ClientAbstract
{
    private string $baseURL;

    public function __construct(private readonly Config $config)
    {
        $this->baseURL = $this->config->baseUrl . '/v1/organizations/' . $this->config->organization;
    }

    public function list(?int $count = null, ?string $startKey = null, bool $includeRevisions = true, bool $includeMetaData = true)
    {

        $url = $this->baseURL . '/environments';
        $response = Http::withBasicAuth($this->config->username, $this->config->password)
            ->get($url);
        return self::handleResponse($response);
    }

    public function get(string $name)
    {
        $url = $this->baseURL . '/environments/' . $name;

        $response = Http::withBasicAuth($this->config->username, $this->config->password)
            ->get($url);

        return self::handleResponse($response);
    }


}
