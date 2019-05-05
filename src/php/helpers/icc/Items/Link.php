<?php

namespace bwt\helpers\icc\Items;

use bwt\App\Libs\Saver\Base\BaseItem;

class Link extends BaseItem
{
    public $url;


    public function toArray()
    {
        return [
            'url' => $this->url,
        ];
    }
}