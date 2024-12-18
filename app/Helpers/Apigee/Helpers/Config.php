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
    public ?string $serverName;

    public function __construct(private readonly ConfigEnum $configEnum)
    {
        $this->configs = require app_path('Helpers/Apigee/config/apigeeConfig.php');
        $this->baseUrl = $this->configs[$this->configEnum->value]['url'];
        $this->username = $this->configs[$this->configEnum->value]['userid'];
        $this->password = $this->configs[$this->configEnum->value]['passwd'];
        $this->organization = $this->configs[$this->configEnum->value]['org'];
        $this->environment = $this->configs[$this->configEnum->value]['env'];
        $this->serverName = $this->configs[$this->configEnum->value]['serverName'] ?? 'server';
        $this->exportFolder = app_path('Helpers/Apigee/ExportedData') . '/' . $this->serverName . "-" . date('Y-m-d-H-i-s');
    }

    public static Config $SingletonInstance;

    public static function getInstance(ConfigEnum $configEnum): Config
    {
        if (!isset(self::$SingletonInstance)) {
            self::$SingletonInstance = new Config($configEnum);
        }
        return self::$SingletonInstance;
    }
}
