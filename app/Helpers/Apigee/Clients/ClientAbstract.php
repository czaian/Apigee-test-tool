<?php

namespace App\Helpers\Apigee\Clients;


use Exception;
use Illuminate\Http\Client\Response;

abstract class ClientAbstract
{
    public static function handleResponse(Response $response)
    {
        if ($response->successful()) {
            //return JSON if JSON or body otherwise
            return (str_contains($response->header('Content-Type'), 'json')) ? $response->json() : $response->body();
        }
        if ($response->serverError()) {
            throw new Exception('Server Error: ' . $response->body());
        }
        if ($response->clientError()) {
            throw new Exception('Client Error: ' . $response->body());
        }
        throw new Exception('Unknown Error: ' . $response->body());
    }
}
