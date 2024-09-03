<?php

namespace App\Services\Api\Shippings\Gateways;

use App\Services\Api\Shippings\BaseApiAdapter;

class NovaPostaAdapter extends BaseApiAdapter
{
    protected $proxy;

    public function __construct($proxy)
    {
        /** @var $proxy NovaPostaProxy */
        $this->proxy = $proxy;
    }

    //for example some flow that need to send parcel
    public function send($data) {
        //for example we check is office working today and create parcel and as a result we get ttn
        $office = $this->proxy->getWarehousesById($data['np_office_id']);
        if($office['working_today'] === true){
            return $this->proxy->createParcel($data);
        }
        // for example return false or we can throw error or return some specific data with info from api error
        return false;
    }
}
