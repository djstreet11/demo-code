<?php

namespace App\Services\Api\Shippings;

abstract class BaseApiAdapter implements BaseApiAdapterInterface
{
    abstract public function send($data);
}
