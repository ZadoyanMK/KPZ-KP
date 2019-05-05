<?php


namespace bwt\App\Libs\Saver\Base\Interfaces;

use bwt\App\Libs\Saver\Base\BaseItem;

interface ValidatorInterface
{
    public function validate(BaseItem $item);
}