<?php

$settings = require_once __DIR__."/../../App/loader.php";
$settings['RABBIT']['active'] = 'ICC_BUSINESS_SAVER';

use bwt\helpers\icc\Savers\SaverBusiness;
use bwt\App\Libs\Saver\Base\Validator;
use bwt\helpers\merchantcircle\Database\PDODatabase;

PDODatabase::setSettings($settings['DB']);
$db = PDODatabase::getInstance();
$db->setPdo();

$validator = new Validator([
    'id' => [
        'length' => 64
    ],
    'url' => [
        'length' => 512
    ],
    'name' => [
        'length' => 64
    ],
    'address_locality' => [
        'length' => 64
    ],
    'address_region' => [
        'length' => 64
    ],
    'postal_code' => [
        'length' => 64
    ],
    'street_address' => [
        'length' => 64
    ],
    'phone' => [
        'length' => 12
    ],
    'clear_phone' => [
        'length' => 12
    ],
    'fax' => [
        'length' => 64
    ],
    'website' => [
        'length' => 512
    ],
    'category' => [
        'length' => 64
    ],
]);

$saver = new SaverBusiness($validator, $settings);
$saver->setConnectionn($db);
$saver->run();
