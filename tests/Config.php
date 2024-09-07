<?php


namespace OneSite\BIDV\Tests;


use Dflydev\DotAccessData\Data;

/**
 * Config
 */
class Config
{
    /**
     * @var null
     */
    static private $_instance = null;

    /**
     * @var Data
     */
    private $configs;

    /**
     * Config constructor.
     */
    private function __construct()
    {
        $configs = require base_path("config.php");

        $this->configs = new Data($configs);
    }

    /**
     * @return static|null
     */
    static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }

    /**
     * @return array|mixed
     */
    public function getConfigs()
    {
        return $this->configs;
    }

}