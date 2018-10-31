<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/8/2
 * Time: 上午10:49
 */

namespace Core\Component\RPC\Standard\Client;


use Core\Component\RPC\Standard\Bean\Caller;

class Task
{
    private $success = null;
    private $fail    = null;
    private $caller;

    function __construct(Caller $caller)
    {
        $this->caller = $caller;
    }

    function success(callable $callable)
    {
        $this->success = $callable;
        return $this;
    }

    function fail(callable $callable)
    {
        $this->fail = $callable;
        return $this;
    }

    /**
     * @return null
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @return null
     */
    public function getFail()
    {
        return $this->fail;
    }

    /**
     * @return Caller
     */
    public function getCaller()
    {
        return $this->caller;
    }

}