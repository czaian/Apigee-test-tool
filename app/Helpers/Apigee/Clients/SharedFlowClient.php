<?php

namespace App\Helpers\Apigee\Clients;

use App\Helpers\Apigee\Helpers\Config;
use Illuminate\Support\Facades\Http;

class SharedFlowClient extends ClientAbstract
{
    private string $baseURL;

    public function __construct(private readonly Config $config)
    {
        $this->baseURL = $this->config->baseUrl . '/v1/organizations/' . $this->config->organization;
    }

    public function list(?int $count = null, ?string $startKey = null, bool $includeRevisions = true, bool $includeMetaData = true)
    {

        $url = $this->baseURL . '/sharedflows';
        $response = Http::withBasicAuth($this->config->username, $this->config->password)
            ->withQueryParameters(
                [
                    'includeRevisions' => $includeRevisions ? 'true' : 'false',
                    'includeMetaData' => $includeMetaData ? 'true' : 'false'
                ]
            )
            ->when(!empty($count), fn($http) => $http->withQueryParameters(['count' => $count]))
            ->when(!empty($startKey), fn($http) => $http->withQueryParameters(['startKey' => $startKey]))
            ->get($url);
        return self::handleResponse($response);
    }

    public function get(string $sharedFlowName, ?int $revision = null)
    {
        $url = $this->baseURL . '/sharedflows/' . $sharedFlowName;
        if (!empty($revision))
            $url .= '/revisions/' . $revision;

        $response = Http::withBasicAuth($this->config->username, $this->config->password)
            ->get($url);

        return self::handleResponse($response);
    }

    public function listDeployments(bool $includeServerStatus = true, bool $includeApiConfig = true)
    {
        $url = $this->baseURL . '/deployments';
        $response = Http::withBasicAuth($this->config->username, $this->config->password)
            ->withQueryParameters([
                'includeServerStatus' => $includeServerStatus ? 'true' : 'false',
                'includeApiConfig' => $includeApiConfig ? 'true' : 'false',
                'sharedFlows' =>  'true'
            ])
            ->get($url);

        return self::handleResponse($response);
    }

    public function download(string $sharedFlowName, int $revision)
    {
        $url = $this->baseURL . '/apis/' . $sharedFlowName . '/revisions/' . $revision;
        $response = Http::withBasicAuth($this->config->username, $this->config->password)
            ->withQueryParameters(['format' => 'bundle'])
            ->get($url);

        return self::handleResponse($response);
    }

}
