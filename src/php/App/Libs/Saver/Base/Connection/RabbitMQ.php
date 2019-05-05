<?php

namespace bwt\App\Libs\Saver\Base\Connection;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQ
{

    private static $settings = [];
    private static $connection = null;

    /**
     * Set connection with rabbitmq
     * @param array $settings
     */
    public static function setConnection(array $settings)
    {
        self::$settings = $settings;
        self::manipulateChannelSettings();
        self::$connection = new AMQPStreamConnection(
            self::$settings['host'],
            self::$settings['port'],
            self::$settings['user'],
            self::$settings['password'],
            self::$settings['vhost'],
            self::$settings['insist'],
            self::$settings['login_method'],
            self::$settings['login_response'],
            self::$settings['locale'],
            self::$settings['connection_timeout'],
            self::$settings['read_write_timeout'],
            self::$settings['context'],
            self::$settings['keepalive'],
            self::$settings['heartbeat']
        );
    }

    /**
     * Need to pass key of channel which config you want to use
     * @param string $key
     * @return mixed
     */
    public static function getChannel(string $key)
    {
        $channel = self::$connection->channel(self::$settings[$key]['channel_id']);
        $channel->basic_qos(self::$settings[$key]['prefetch_size'], self::$settings[$key]['batch'], self::$settings[$key]['a_global']);
        $channel->queue_declare(self::$settings[$key]['queue'], self::$settings[$key]['passive'], self::$settings[$key]['durable'], self::$settings[$key]['exclusive'],
            self::$settings[$key]['auto_delete'], self::$settings[$key]['nowait'], self::$settings[$key]['arguments'], self::$settings[$key]['ticket']);
        return $channel;
    }

    /**
     * Close connection with Rabbit
     */
    public static function close()
    {
        if(self::$connection !== null) {
            self::$connection->close();
        }
    }

    /**
     * Set settings to channels
     */
    private static function manipulateChannelSettings()
    {
        $settings = self::$settings;
        foreach(self::$settings['channels'] as $key => $val) {
            $tmp = [
              'channel_id' => $val['channel_id'] ?? $settings['default']['channel_id'],
              'prefetch_size' => $val['prefetch_size'] ?? $settings['default']['channel_id'],
              'batch' => $val['batch'] ?? $settings['default']['batch'],
              'a_global' => $val['a_global'] ?? $settings['default']['a_global'],
              'queue' => $val['queue'] ?? $settings['default']['queue'],
              'passive' => $val['passive'] ?? $settings['default']['passive'],
              'durable' => $val['durable'] ?? $settings['default']['durable'],
              'exclusive' => $val['exclusive'] ?? $settings['default']['exclusive'],
              'auto_delete' => $val['auto_delete'] ?? $settings['default']['auto_delete'],
              'nowait' => $val['$nowait'] ?? $settings['default']['nowait'],
              'consumer_tag' => $val['consumer_tag'] ?? $settings['default']['consumer_tag'],
              'no_local' => $val['no_local'] ?? $settings['default']['no_local'],
              'no_ack' => $val['no_ack'] ?? $settings['default']['no_ack'],
              'arguments' => $val['arguments'] ?? $settings['default']['arguments'],
              'ticket' => $val['ticket'] ?? $settings['default']['ticket'],
              'sleep_time' => $val['sleep_time'] ?? $settings['default']['sleep_time'],
              'count_of_tries' => $val['count_of_tries'] ?? $settings['default']['count_of_tries']
            ];
            self::$settings[$key] = $tmp;
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    public static function getSettings(string $key)
    {
        return self::$settings[$key];
    }
}