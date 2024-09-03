<?php

namespace App\Services\Api\Shippings\Gateways;

use App\Services\Api\Shippings\BaseApiAdapter;

class UkrPoshtaAdapter extends BaseApiAdapter
{
    protected $proxy;

    public function __construct($proxy)
    {
        $this->proxy = $proxy;
    }

    public function send($data) {
        $parcel = $this->proxy->prepareData($data);
        $response = $this->proxy->sendParcel($parcel);
        return $response;
    }
}
