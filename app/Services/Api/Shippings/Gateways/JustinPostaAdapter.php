<?php

namespace App\Services\Api\Shippings\Gateways;

use App\Services\Api\Shippings\BaseApiAdapter;

class JustinPostaAdapter extends BaseApiAdapter
{
    protected $proxy;

    public function __construct($proxy)
    {
        $this->proxy = $proxy;
    }

    public function send($data) {
        return $this->proxy->sendBox($data);
    }
}
