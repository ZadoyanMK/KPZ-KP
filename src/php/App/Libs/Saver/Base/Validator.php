<?php


namespace bwt\App\Libs\Saver\Base;

use bwt\App\Libs\Saver\Base\Interfaces\ValidatorInterface;

class Validator implements ValidatorInterface{

    private $settings = [];

    /**
     * Default parameters for min, max value for int and float
     */
    private const MAX_INT = 9223372036854775807;
    private const MIN_INT = 0;
    private const MIN_FLOAT = -9999;
    private const MAX_FLOAT = 9999;

    /**
     * Remove unicode from string
     * @param $string
     * @param string $replacement
     * @return null|string|string[]
     */
    private function replace4byte($string, $replacement = '')
    {
        return preg_replace('%(?:
              \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
        )%xs', $replacement, $string);
    }

    /**
     * Validator constructor.
     * Receive an array of settings. Each key must match the field of item.
     * Settings are:
     *  type (available: string, int, float)
     *  length (if string it will check length of string, if number(float | int) - will compare it)
     *  min_length(if string - will ignore, if number - will compare it)
     *  toNull (default - false) - you can pass this key if you want that field was set to null, if it false (for
     * example 0, "", false)
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Validate item if error occured will throw \Exception
     * @param BaseItem $item
     * @throws \Exception
     */
    public function validate(BaseItem $item)
    {
        foreach($this->settings as $key => $val) {
            if(property_exists($item, $key)) {
                $val['type'] = $val['type'] ?? 'string';
                $typeToLead = empty($val['toNull']) ? $val['type'] : null;
                switch($val['type']){
                    case 'int':
                        $length = $val['length'] ?? self::MAX_INT;
                        $min_length = $val['min_length'] ?? self::MIN_INT;
                        $item->$key = intval($item->$key);
                        if($item->$key > $length) {
                            $item->$key = $length;
                        }
                        if($item->$key < $min_length) {
                            $item->$key = $min_length;
                        }
                        break;
                    case 'float':
                        $length = $val['length'] ?? self::MAX_FLOAT;
                        $min_length = $val['min_length'] ?? self::MIN_FLOAT;
                        $item->$key = floatval($item->$key);
                        if($item->$key > $length) {
                            $item->$key = $length;
                        }
                        if($item->$key < $min_length) {
                            $item->$key = $min_length;
                        }
                        break;
                    default:
                        $length = empty($val['length']) ? strlen($item->$key) : $val['length'];
                        $item->$key = $this->replace4byte($item->$key);
                        $item->$key = substr(trim($item->$key), 0, $length);
                        break;
                }
                if(!$item->$key && $typeToLead == null) {
                    $item->$key = null;
                }
            } else {
                throw new \Exception("Undefined key ".$key." in array");
            }
        }
    }

    /**
     * @param array $settings
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return array
     */
    public function getSettings() : array
    {
        return $this->settings;
    }

}