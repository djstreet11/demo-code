<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliverySendRequest;
use App\Services\DeliveryService;

class DeliveryController extends Controller
{
    private DeliveryService $deliveryService;

    public function __construct(
        DeliveryService $deliveryService,
    ) {
        $this->deliveryService = $deliveryService;
    }

    public function sendParcel(DeliverySendRequest $request){
        $this->deliveryService->sendParcel($request->all());
    }
}
