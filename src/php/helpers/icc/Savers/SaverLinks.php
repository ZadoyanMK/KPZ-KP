<?php

namespace bwt\helpers\icc\Savers;

use bwt\App\Libs\Saver\Base\BaseSaver;
use bwt\App\Libs\Saver\Base\Interfaces\SaverInterface;
use bwt\helpers\icc\Items\Link;


class SaverLinks extends BaseSaver implements SaverInterface
{
    protected $item = Link::class;

    public function insert(array $items)
    {
        $push = [];
        foreach($items as $val) {
            $push[] = $val->toArray();
        }

        $this->db->insertIgnore($push, 'businesses');
    }
}