<?php

namespace App\Helpers\Apigee\Helpers;

class Config
{
    public /** @var array{from: array{url: string, userid: string, passwd: string, org: string, env: string}, to: array{url: string, userid: string, passwd: string, org: string, env: string}} */
    array $configs;
    public string $baseUrl;
    public string $organization;
    public string $username;
    public string $password;
    public ?string $environment;
    public ?string $exportFolder;

    public function __construct(private readonly ConfigEnum $configEnum)
    {
        $this->configs = require app_path('Helpers/Apigee/config/apigeeConfig.php');
        $this->baseUrl = $this->configs[$this->configEnum->value]['url'];
        $this->username = $this->configs[$this->configEnum->value]['userid'];
        $this->password = $this->configs[$this->configEnum->value]['passwd'];
        $this->organization = $this->configs[$this->configEnum->value]['org'];
        $this->environment = $this->configs[$this->configEnum->value]['env'];
        $this->exportFolder = app_path('Helpers/Apigee/ExportedData');
    }
}
