<?php
/**
 * Created by PhpStorm.
 * User: safer
 * Date: 2018/11/12
 * Time: 23:44:07
 */

namespace Core\Component\Schedule;


use Core\Conf\Event;

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

    function _event()
    {
        Event::getInstance()->frameInitialize();
        Event::getInstance()->frameInitialized();
    }

    /**
     *创建并启动一个swoole http server
     */
    function startServer()
    {
        Dispatcher::getInstance()->dispatch();
    }
}