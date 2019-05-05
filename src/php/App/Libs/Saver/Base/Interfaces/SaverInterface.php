<?php


namespace bwt\App\Libs\Saver\Base\Interfaces;


interface SaverInterface
{
    /**
     * Receive an array of items that need to insert into DB
     * @param array $items
     */
    public function insert(array $items);

    /**
     * Called when impossible make data correct (for example unreadeable json)
     * @param \Exception $e
     * @return mixed
     */
    public function validationError($e);
}