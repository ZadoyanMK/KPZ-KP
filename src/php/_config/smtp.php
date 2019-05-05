<?php


return [
    'host' => env('MAIL_HOST'),
    'port' => env('MAIL_PORT', 465),
    'user' => env('MAIL_USERNAME'),
    'password' => env('MAIL_PASSWORD'),
    'encryption' => env('MAIL_ENCRYPTION'),
    'recipient' => env('MAIL_RECIPIENT')
];