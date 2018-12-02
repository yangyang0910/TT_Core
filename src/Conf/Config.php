<?php

namespace Core\Conf;

use Core\Component\Di;
use Core\Component\Spl\SplArray;
use Core\Component\SysConst;
use Core\AutoLoader;

/**
 * Class Config
 * @package Core\Conf
 */
class Config
{
    /**
     * @var
     */
    private static $instance;
    /**
     * @var SplArray
     */
    protected $conf;

    /**
     * Config constructor.
     */
    function __construct()
    {
        $appConf    = array_replace_recursive($this->appCommonConf(), $this->appConf());
        $this->conf = $this->sysConf() + $appConf;
        $this->conf = new SplArray($this->conf);
    }

    /**
     * @return Config
     */
    static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * @param $keyPath
     *
     * @return array|mixed|null
     */
    function getConf($keyPath)
    {
        return $this->conf->get($keyPath);
    }

    /**
     * 在server启动以后，无法动态的去添加，修改配置信息（进程数据独立）
     *
     * @param $keyPath
     * @param $data
     */
    function setConf($keyPath, $data)
    {
        $this->conf->set($keyPath, $data);
    }

    /**
     * @return array
     */
    private function sysConf()
    {
        return [];
    }

    /**
     * @return array|bool
     */
    private function appConf()
    {
        $confPath = '/App/' . APP_NAME . '/Conf';
        $confFile = $confPath . '/env/' . APP_ENV . '.php';
        $conf     = AutoLoader::getInstance()->requireFile($confFile);
        return $conf ?: [];
    }

    private function appCommonConf()
    {
        $confFile = '/App/Common/Conf/Conf.php';
        $conf     = AutoLoader::getInstance()->requireFile($confFile);
        return $conf ?: [];
    }
}