<?php

return [
    /**
     * Settings for AMQP connection
     */
  'host' => env('RABBIT_HOST', '127.0.0.1'),
  'port' => env('RABBIT_PORT', 5672),
  'user' => env('RABBIT_USER', 'guest'),
  'password' => env('RABBIT_PASSWORD', 'guest'),
  'vhost' => '/',
  'insist' => false,
  'login_method' => 'AMQPLAIN',
  'login_response' => null,
  'locale' => 'en_US',
  'connection_timeout' => 3.0,
  'read_write_timeout' => 3.0,
  'context' => null,
  'keepalive' => false,
  'heartbeat' => 0,
    /**
     * Default settings for channels
     */
  'default' => [
      'batch' => 800,
      'sleep_time' => 3.25, //time for which script will stop when error occured
      'channel_id' => null,
      'prefetch_size' => null,
      'a_global' => null,
      'queue' => '',
      'passive' => false,
      'durable' => true,
      'exclusive' => false,
      'auto_delete' => false,
      'nowait' => false,
      'consumer_tag' => '',
      'no_local' => false,
      'no_ack' => false,
      'arguments' => [],
      'ticket' => null,
      'count_of_tries' => 3 //count of tries to execute insert method when error occured
  ]
];
