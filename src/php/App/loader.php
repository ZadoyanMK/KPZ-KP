<?php

require_once __DIR__.'/../vendor/autoload.php';

define("CONF", __DIR__."/../_config");

use Dotenv\Dotenv;

$dotenv = new Dotenv(CONF);
$dotenv->load();
$settings = [];
$settings["DB"] = require_once CONF."/db.php";
$settings["RABBIT"] = require_once CONF."/rabbit.php";
$settings["RABBIT"]['channels'] = require_once CONF."/channels.php";
$settings["SMTP"] = require_once CONF."/smtp.php";

date_default_timezone_set(env('TIMEZONE', "Europe/Kiev"));

return $settings;