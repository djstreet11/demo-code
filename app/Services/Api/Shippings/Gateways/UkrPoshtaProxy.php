<?php

namespace App\Services\Api\Shippings\Gateways;

class UkrPoshtaProxy
{
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function prepareData($data) { }
    public function sendParcel($data) { }

}
