<?php
/**
 * Created by PhpStorm.
 * User: safer
 * Date: 2018/11/12
 * Time: 23:44:07
 */

namespace Core\Component\Crontab;

use Core\Component\Di;
use Core\Component\Error\Trigger;
use Core\Component\SuperClosure;
use Core\Component\SysConst;

/**
 * Class Server
 * @package Core\Swoole
 */
class Server
{
    /**
     * @var
     */
    protected static $instance;

    /**
     *
     * 仅仅用于获取一个服务实例
     *
     * @return static
     */
    static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Server constructor.
     */
    function __construct()
    {

    }

    /**
     *创建并启动一个swoole http server
     */
    function startServer()
    {
        Dispatcher::getInstance()->dispatch();
    }
}