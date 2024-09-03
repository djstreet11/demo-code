<?php

namespace App\Services\Api\Shippings\Gateways;

class JustinPostaProxy
{
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function sendBox($data)  { }
}
