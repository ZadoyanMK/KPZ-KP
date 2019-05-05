<?php


namespace bwt\App\Libs\Saver\Base;


class BaseItem
{
    public $delivery_tag;

    /**
     * BaseItem constructor.
     * @param $json
     * @throws \Exception
     */
    public function __construct($json)
    {
        $arr = json_decode($json, true);
        if($arr == null){
            throw new \Exception("Unreadeable json data.");
        }
        foreach($arr as $key => $val) {
            if(property_exists($this, $key)) {
                $this->$key = $val;
            }
        }
    }

    /**
     * Will called, before item will be passed to the validator
     */
    public function beforeValidation()
    {

    }

    /**
     * Will called, when item will be valid.
     */
    public function afterValidation()
    {

    }
}

