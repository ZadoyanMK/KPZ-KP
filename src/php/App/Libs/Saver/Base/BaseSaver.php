<?php


namespace bwt\App\Libs\Saver\Base;

use PhpAmqpLib\Message\AMQPMessage;
use bwt\App\Libs\Saver\Base\Interfaces\ValidatorInterface;
use bwt\App\Libs\Saver\Base\Interfaces\NotifierInterface;
use bwt\App\Libs\Saver\Base\Connection\RabbitMQ;
use bwt\App\Libs\Database\Database;

class BaseSaver
{
    private $validator;
    private $sender;
    private $items = [];
    private $settings = [];
    private $channel;
    private $channel_settings = [];
    protected $db = null;

    /**
     * BaseSaver constructor.
     * Set parameters for notification, validator, settings and rabbitmq connection
     * Set callback function
     * @param ValidatorInterface $validator
     * @param array $settings
     */
    public function __construct(ValidatorInterface $validator, array $settings)
    {
        $this->settings = $settings;
        $this->validator = $validator;
        RabbitMQ::setConnection($settings['RABBIT']);
        $this->channel = RabbitMQ::getChannel($settings['RABBIT']['active']);
        $this->channel_settings = RabbitMQ::getSettings($settings['RABBIT']['active']);
        $this->makeNumChannelSettings();
        $this->setNotifySender(new Notifier($settings['SMTP']));
    }


    /**
     * Сonverts seconds to microseconds to further call the usleep function
     */
    private function makeNumChannelSettings()
    {
        $this->channel_settings['batch'] = intval($this->channel_settings['batch']);
        $time = explode(".", $this->channel_settings['sleep_time']);
        $time[0] = intval($time[0]) * pow(10,6);
        if(isset($time[1])) {
            $time[1] = floatval("0." . intval($time[1])) * pow(10, 6);
        }
        else {
            $time[1] = 0;
        }
        $this->channel_settings['sleep_time'] = $time[0] + $time[1];
    }

    /**
     * Will called send method of NotifierInterface object
     * @param string $subject
     * @param string $message
     */
    private function sendNotification(string $subject, string $message)
    {
        $this->sender->send($subject, $message);
    }


    /**
     * Сalled when error occured in insert method, trying to execute insert method without error for count_of_tries times
     * (this setting need to set in config)
     * in queue will not be achieved, (if this happens - will send notification and stop script.)
     */
    private function tryExecute()
    {
        $sleep_time = $this->channel_settings['sleep_time'] ?? 1;
        $tries = $this->channel_settings['count_of_tries'];

        for($i = 0; $i < $tries; $i++){
            try{
                $this->insert($this->items);
                return;
            } catch(\Exception $e){
                $error = $e->getMessage();
                echo "[error] ",date('H:i:s')," Error occured when trying to execute insert method: ",$error, "\n";
                echo "Sleeping for ".$sleep_time. " seconds","\n";
                usleep($this->channel_settings['sleep_time']);
            }
        }

        $errors = print_r(debug_backtrace(), true);
        $this->sendNotification("Stop script", $errors);
        echo "\n",$errors;
        echo "\nCRITICAL ERROR, STOP SCRIPT WITH CODE 2";
        exit(2);
    }

    /**
     * Compare count of items and number of batch
     * @return bool
     */
    private function enoughForInsert()
    {
        $items_count = count($this->items);

        if($items_count >= $this->channel_settings['batch']) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Confirm receipt of messages and clears array of items.
     */
    private function clear()
    {
        foreach($this->items as $item) {
            $this->channel->basic_ack($item->delivery_tag);
        }

        $this->items = [];
    }

    /**
     * Will called when new message from rabbit will receive
     * Callback function trying to create new item, validate it and checks the need of an insert in the database
     * @param AMQPMessage $msg
     */
    public function callback(AMQPMessage $msg)
    {
        try{
            $item = new $this->item($msg->body);
            $item->beforeValidation();
            $this->validator->validate($item);
            $item->delivery_tag = $msg->delivery_info['delivery_tag'];
            $this->items[] = $item;
            $item->afterValidation();
        } catch(\Exception $e){
            $this->validationError($e);
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            return;
        }
        if($this->enoughForInsert()){
            try{
                $this->insert($this->items);
            } catch(\Exception $e){
                $this->sendNotification('Error in parser', $e->getMessage());
                $this->tryExecute();
            } finally {
                $this->clear();
                echo "Cleared ",date("H:i:s"),"\n";
            }
        }
    }

    /**
     * Called when impossible make data correct (for example unreadeable json)
     * @param $error
     */
    public function validationError($error)
    {
        echo "[e]", $error->getMessage(), "\n";
    }

    /**
     * Object that will send message (by default on email)
     * @param NotifierInterface $sender
     */
    public function setNotifySender(NotifierInterface $sender)
    {
        $this->sender = $sender;
    }

    /**
     * You can set another connection to Database, by default it will use \bwt\App\Libs\Database\Database
     * @param $database
     */
    public function setConnectionn($database)
    {
        $this->db = $database;
    }


    /**
     * Start consuming the queue
     */
    public function run()
    {
        if($this->db === null) {
            Database::setSettings($this->settings['DB']);
            $this->db = Database::getInstance();
        }

        $this->channel->basic_consume($this->channel_settings['queue'], $this->channel_settings['consumer_tag'],
            $this->channel_settings['no_local'], $this->channel_settings['no_ack'], $this->channel_settings['exclusive'],
            $this->channel_settings['nowait'], [$this, 'callback'], $this->channel_settings['ticket'],
            $this->channel_settings['arguments']);
        echo "Start consuming ", date("H:i:s"), "\n";

        while(count($this->channel->callbacks)) {
            $this->channel->wait();
        }

        echo "\n Channel closing. Exiting from program";
        $this->channel->close();
        RabbitMQ::close();
    }

}

