<?php

$settings = require_once __DIR__."/../../App/loader.php";
$settings['RABBIT']['active'] = 'ICC_LINK_SAVER';

use bwt\helpers\icc\Savers\SaverLinks;
use bwt\App\Libs\Saver\Base\Validator;
use bwt\helpers\merchantcircle\Database\PDODatabase;

PDODatabase::setSettings($settings['DB']);
$db = PDODatabase::getInstance();
$db->setPdo();

$validator = new Validator([
    'url' => [
        'length' => 512
    ]
]);


$saver = new SaverLinks($validator, $settings);
$saver->setConnectionn($db);
$saver->run();
