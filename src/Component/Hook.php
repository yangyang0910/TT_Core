<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/23
 * Time: 上午12:05
 */

namespace Core\Component;


use Core\Swoole\Task\TaskManager;
use Core\Swoole\Server;

/**
 * Class Hook
 * @package Core\Component
 */
class Hook
{
    /**
     * @var
     */
    protected static $instance;
    /**
     * @var array
     */
    private $eventList = [];

    /**
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
     * @param          $event
     * @param callable $callback
     * @return $this
     */
    function listen($event, callable $callback)
    {
        $this->eventList[$event] = $callback;
        return $this;
    }

    /**
     * @param       $event
     * @param array ...$arg
     * @throws \Exception
     */
    function event($event, ...$arg)
    {
        if (isset($this->eventList[$event])) {
            $handler = $this->eventList[$event];
            try {
                call_user_func_array($handler, $arg);
            } catch (\Exception $exception) {
                throw $exception;
            }
        }
    }
}