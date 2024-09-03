<?php

namespace App\Services;

use App\Enums\Delivery;

class DeliveryService
{
    //get classes for adapter and proxy depend on name (slug) of delivery service
    public function getAdapter(Delivery $apiType)
    {
        $adapterClass = 'App\\Services\\Shippings\\Gateways\\' . ucfirst($apiType->value) . 'Adapter';
        $proxyClass = 'App\\Services\\Shippings\\Gateways\\' . ucfirst($apiType->value) . 'Proxy';

        if (!class_exists($adapterClass)) {
            throw new \Exception("Adapter class $adapterClass does not exist");
        }
        if (!class_exists($proxyClass)) {
            throw new \Exception("Proxy class $adapterClass does not exist");
        }

        return new $adapterClass(new $proxyClass());
    }

    public function sendParcel($data){

        $apiType = Delivery::fromSlug($data['api_slug']); //get slug of delivery service
        $adapter = $this->getAdapter($apiType); //get classes of delivery service
        return $adapter->send($data);
    }
}
