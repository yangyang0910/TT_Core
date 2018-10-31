<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/5
 * Time: 下午5:20
 */

namespace Core\Component;


use Core\Swoole\Server;

/**
 * Class Barrier
 * @package Core\Component
 */
class Barrier
{
    /**
     * @var array
     */
    private $tasks = [];
    /**
     * @var array
     */
    private $maps = [];
    /**
     * @var array
     */
    private $results = [];

    /**
     * @param $taskName
     * @param $callable
     * @return bool
     */
    function add($taskName, $callable)
    {
        if ($callable instanceof \Closure) {
            try {
                $callable = new SuperClosure($callable);
            } catch (\Exception $exception) {
                trigger_error("async task serialize fail ");
                return false;
            }
        }
        $this->tasks[$taskName] = $callable;
        return true;
    }

    /**
     * @param float $timeout
     * @return array
     */
    function run($timeout = 0.5)
    {
        $temp = [];
        foreach ($this->tasks as $name => $task) {
            $temp[]       = $task;
            $this->maps[] = $name;
        }
        if (!empty($temp)) {
            $ret = Server::getInstance()->getServer()->taskWaitMulti($temp, $timeout);
            if (!empty($ret)) {
                //极端情况下  所有任务都超时
                foreach ($ret as $index => $result) {
                    $this->results[$this->maps[$index]] = $result;
                }
            }
        }
        return $this->results;
    }

    /**
     * @return array
     */
    function getResults()
    {
        return $this->results;
    }

    /**
     * @param $taskName
     * @return mixed|null
     */
    function getResult($taskName)
    {
        if (isset($this->results[$taskName])) {
            return $this->results[$taskName];
        } else {
            return null;
        }
    }
}