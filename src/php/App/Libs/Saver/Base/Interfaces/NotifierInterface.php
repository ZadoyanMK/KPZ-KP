<?php


namespace bwt\App\Libs\Saver\Base\Interfaces;


interface NotifierInterface
{
    /**
     * @param string $subject
     * @param string $message
     */
    public function send(string $subject, string $message);
}