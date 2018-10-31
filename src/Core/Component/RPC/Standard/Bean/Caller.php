<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/27
 * Time: 下午10:24
 */

namespace Core\Component\RPC\Standard\Bean;


use Core\Component\Spl\SplBean;
use Core\Utility\Random;

class Caller extends SplBean
{
    protected $client;
    protected $service;
    protected $action;
    protected $args = [];
    protected $requestId;

    /**
     * @return mixed
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @param mixed $requestId
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param mixed $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param array $args
     */
    public function setArgs(array $args)
    {
        $this->args = $args;
    }

    function setArg($key, $val)
    {
        $this->args[$key] = $val;
    }

    function getArg($key)
    {
        if (isset($this->args[$key])) {
            return $this->args[$key];
        }
        return null;
    }

    protected function initialize()
    {
        if (empty($this->requestId)) {
            $this->requestId = Random::character(10);
        }
    }

}