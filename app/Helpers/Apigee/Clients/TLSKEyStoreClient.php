<?php

namespace App\Helpers\Apigee\Clients;

use App\Helpers\Apigee\Helpers\Config;
use Illuminate\Support\Facades\Http;

class TLSKEyStoreClient extends ClientAbstract
{
    private string $baseURL;

    public function __construct(private readonly Config $config)
    {
        $this->baseURL = $this->config->baseUrl . '/v1/organizations/' . $this->config->organization;
    }

    public function list(string $environmentName)
    {

        $url = $this->baseURL . '/environments/' . $environmentName . '/keystores';
        $response = Http::withBasicAuth($this->config->username, $this->config->password)
            ->get($url);
        return self::handleResponse($response);
    }

    public function get(string $environmentName, $name, $expand = true)

    {
        $url = $this->baseURL . '/environments/' . $environmentName . '/keystores/' . $name;

        $response = Http::withBasicAuth($this->config->username, $this->config->password)
            ->withQueryParameters(['expand' => $expand ? 'true' : 'false'])
            ->get($url);

        return self::handleResponse($response);
    }

    public function listCerts(string $environmentName, $name)
    {
        $url = $this->baseURL . '/environments/' . $environmentName . '/keystores/' . $name . '/certs';
        $response = Http::withBasicAuth($this->config->username, $this->config->password)
            ->get($url);
        return self::handleResponse($response);
    }

    public function getCert(string $environmentName, $name, $certName)
    {
        $url = $this->baseURL . '/environments/' . $environmentName . '/keystores/' . $name . '/certs/' . $certName;
        $response = Http::withBasicAuth($this->config->username, $this->config->password)
            ->get($url);
        return self::handleResponse($response);
    }

    public function exportCert(string $environmentName, $name, $certName)
    {
        $url = $this->baseURL . '/environments/' . $environmentName . '/keystores/' . $name . '/certs/' . $certName.'/export';
        $response = Http::withBasicAuth($this->config->username, $this->config->password)
            ->get($url);
        return self::handleResponse($response);
    }


}
