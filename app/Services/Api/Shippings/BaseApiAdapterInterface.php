<?php

namespace App\Services\Api\Shippings;


interface BaseApiAdapterInterface
{
    public function send($data);
}
