<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午12:44
 */

namespace Core\Component\Crontab;

use Core\Conf\Config;
use Core\Conf\Event;
use Core\AbstractInterface\ABaseController as Controller;
use Core\Component\Di;
use Core\Component\SysConst;

/**
 * Class Dispatcher
 * @package Core\Http
 */
class Dispatcher
{
    /**
     * @var
     */
    protected static $selfInstance;
    /**
     * @var string
     */
    protected $controllerSuffix = 'Controller';
    /**
     * @var array
     */
    protected $controllerPool = [];
    /**
     * @var array|bool|mixed|null
     */
    protected $useControllerPool = false;
    /**
     * @var array
     */
    protected $controllerMap = [];
    /**
     * @var array
     */
    protected $serverParamMap = [];

    /**
     * @return Dispatcher
     */
    static function getInstance()
    {
        if (!isset(self::$selfInstance)) {
            self::$selfInstance = new Dispatcher();
        }
        return self::$selfInstance;
    }

    /**
     * Dispatcher constructor.
     */
    function __construct()
    {
    }

    /**
     *
     */
    function dispatch()
    {
        $pathInfo       = CRON_SREVER_ROUTE;
        $list           = explode("/", $pathInfo);
        $controllerPath = APP_NAME . "\\Crontab";
        $appName        = null;
        $controllerName = null;
        $actionName     = null;
        $finalClass     = null;
        if (preg_match('/v\d/', current($list))) { //版本控制
            $controllerPath .= ucfirst(array_shift($list));
            reset($list);
        }
        if (2 > count($list)) {
            exit('cron route size must be more than the 2');
        }
        list($controllerName, $actionName) = $list;
        $finalClass = $controllerPath . '\\' . ucfirst($controllerName) . 'Controller';
        if (false === class_exists($finalClass)) {
            exit($finalClass . ' not found');
        }
        $class = new $finalClass;
        if (false === method_exists($class, $actionName)) {
            exit($finalClass . '\\' .$actionName . ' not found');
        }
        $class->$actionName();
    }
}