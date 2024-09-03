<?php

namespace App\Services\Api\Shippings\Gateways;

use Illuminate\Support\Facades\Log;

class NovaPostaProxy
{

    //set urls, keys, auth... from ENV for example
    public string $key = '';
    public string $base_api_url = "";

    public function __construct()
    {

    }

    // generate body and send request to api and return response
    public function call( ) {
        return ['working_today'=>true];
    }

    //for example some specific methods that need to be called for send parcel
    //get info is it working today and create parcel for to get ttn number
    //also add info to log from enter data and from response, also we can set log for each delivery service
    public function getWarehousesById($id) {
        Log::info('getWarehousesById with parameters: ', $id);
        $response = $this->call();
        Log::info('getWarehousesById response: ', $response);
        return $response;
    }
    public function createParcel($data) { return ['ttn'=>'12345']; }

    //list of specific methods of this delivery service API
    public function getAreas() { }
    public function getCities( ) { }
    public function getWarehouses( ) { }
    public function getSettlements() { }

}
