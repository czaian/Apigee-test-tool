<?php

namespace App\Helpers\Apigee\Helpers;

use App\Helpers\Apigee\Clients\EnvironmentClient;
use Illuminate\Console\Command;

abstract class ServiceAbstract
{
    protected Command $command;
    protected StoragePaths $storage;
    protected Config $config;
    private EnvironmentClient $environmentClient;

    public function __construct(ConfigEnum $configKey = ConfigEnum::ConfigOriginKey)
    {
        $this->command = app('currentCommand');
        $this->config = Config::getInstance($configKey);
        $this->storage = new StoragePaths($this->config);
        $this->environmentClient = new EnvironmentClient($this->config);
    }

    /**
     * @param string|array $string
     * @param string $type info|error|warn|comment
     * @return void
     */
    public function commandLog(string|array $string, string $type = 'info'): void
    {
        $command = app('currentCommand');
        $string = is_string($string) ? $string : json_encode($string, JSON_PRETTY_PRINT);
        $command->$type($string);
    }

    public function listEnvironments(): array
    {
        return $this->environmentClient->list();
    }

    abstract public function exportAll();
}
