<?php


namespace bwt\App\Libs\Database;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database
{
    private static $instance = null;
    private static $settings = [];
    /**
     * @var Capsule
     */
    private $capsule;

    /**
     * Database constructor.
     * @param array $settings
     */
    private function __construct()
    {
        $this->capsule = new Capsule;
        $this->capsule->addConnection(self::$settings);
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();
    }

    /**
     * @return Database
     */
    public static function getInstance()
    {
        if(self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }


    /**
     * @return Capsule
     */
    public function getCapsule()
    {
        return $this->capsule;
    }

    /**
     * @param array $settings
     */
    public static function setSettings(array $settings)
    {
        self::$settings = $settings;
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

}