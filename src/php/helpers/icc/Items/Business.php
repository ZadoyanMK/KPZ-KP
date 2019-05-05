<?php

namespace bwt\helpers\icc\Items;

use bwt\App\Libs\Saver\Base\BaseItem;

class Business extends BaseItem
{
    public $id;
    public $url;
    public $name;

    public $street_address;
    public $address_locality;
    public $address_region;
    public $postal_code;

    public $phone;
    public $clear_phone;

    public $website;
    public $fax;
    public $category;
    public $parse_status;


    public function toArray()
    {
        $status = $this->parse_status;
        if ($this->parse_status == NULL || $this->parse_status == "" || $this->parse_status == 'None'){
            $status = "3";
        }

        return [
            'id' => $this->id,
            'url' => $this->url,
            'name' => $this->name,

            'address_locality' => $this->address_locality,
            'address_region' => $this->address_region,
            'postal_code' => $this->postal_code,
            'street_address' => $this->street_address,

            'phone' => $this->phone,
            'clear_phone' => $this->clear_phone,

            'fax' => $this->fax,
            'website' => $this->website,
            'category' => $this->category,
            'parse_status' => $status,
        ];
    }
}
