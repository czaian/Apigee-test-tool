<?php

namespace App\Helpers\Apigee\Helpers;

abstract class ServiceAbstract
{
    public static function commandLog(string|array $string): void
    {
        $command = app('currentCommand');
        $string = is_string($string) ? $string : json_encode($string, JSON_PRETTY_PRINT);
        $command->info($string);
    }
}
