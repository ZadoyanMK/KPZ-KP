<?php

namespace bwt\helpers\icc\Savers;

use bwt\App\Libs\Saver\Base\BaseSaver;
use bwt\App\Libs\Saver\Base\Interfaces\SaverInterface;
use bwt\helpers\icc\Items\Business;
use bwt\helpers\merchantcircle\Database\PDODatabase;


class SaverBusiness extends BaseSaver implements SaverInterface
{
    protected $item = Business::class;

    public function insert(array $items)
    {
        $push = [];

        $phones = [];
        foreach($items as $val) {
            $arr = $val->toArray();
            if ($arr['phone'] != 'NULL' && $arr['phone'] != '' && $arr['phone'] != NULL && $arr['parse_status'] != "3")
            {
                if (!in_array($arr['clear_phone'], $phones))
                {
                    $phones[] = $arr['clear_phone'];

                }
            }
        }
        
        $phones_str = implode(',', $phones);
        $phones_str = str_replace(',,', ',', $phones_str);
        if (substr($phones_str, -1) == ','){
            $replacement = '';
            substr($phones_str, -1).$replacement;
        }

        $pdo = $this->db->getPdo();
        $results = [];

        if ($phones_str){
            $results = $pdo->query("SELECT id, clear_phone FROM businesses WHERE clear_phone IN(".$phones_str.")");
            $results = $results->fetchAll(\PDO::FETCH_ASSOC);
        }

        foreach ($items as $key => $val)
        {
            if ($val->clear_phone != 'NULL' && $val->clear_phone != '' && $val->clear_phone != NULL) {
                $isInDB = false;
                foreach ($results as $result) {
                    if ($val->clear_phone == $result['clear_phone'] && $val->id != $result['id']){
                        $isInDB = true;
                        break;
                    }
                }
                if ($isInDB) {
                    $val->clear_phone = NULL;
                } else {
                    foreach ($items as $key2 => $val2){
                        if ($key == $key2){
                            continue;
                        }
                        if ($val2->clear_phone == $val->clear_phone){
                            $val->clear_phone = NULL;
                        }
                    }
                }
            }

            $arr = $val->toArray();
            if ($arr['parse_status'] != "3"){
                $push[] = array_replace($arr,
                    array_fill_keys(
                        array_keys($arr, 'NULL'),
                        NULL
                    ),
                    array_fill_keys(
                        array_keys($arr, ''),
                        NULL
                    )
                );
            }

        }
        
        if ($push){
            $this->db->insertOrUpdate($push, 'businesses', ['id', 'url']);
        }
    }
}
