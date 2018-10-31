<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 上午2:33
 */

namespace Core\Swoole\Pipe;


/**
 * Class CommandList
 * @package Core\Swoole\Pipe
 */
class CommandList
{
    /**
     * @var array
     */
    private $list = [];

    /**
     * @param          $command
     * @param callable $handler
     * @return $this
     */
    function add($command, callable $handler)
    {
        $this->list[$command] = $handler;
        return $this;
    }

    /**
     * @param callable $handler
     * @return $this
     */
    function setDefaultHandler(callable $handler)
    {
        $this->list['__DEFAULT__'] = $handler;
        return $this;
    }

    /**
     * @param $command
     * @return mixed|null
     */
    function getHandler($command)
    {
        if (isset($this->list[$command])) {
            return $this->list[$command];
        } elseif (isset($this->list['__DEFAULT__'])) {
            return $this->list['__DEFAULT__'];
        } else {
            return NULL;
        }
    }
}