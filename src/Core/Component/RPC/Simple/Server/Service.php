<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 下午3:48
 */

namespace Core\Component\RPC\Simple\Server;

/**
 * Class Service
 * @package Core\Component\RPC\Simple\Server
 */
class Service
{
    /**
     * @var
     */
    protected $actionRegisterClass;

    /**
     * @return mixed
     */
    public function getActionRegisterClass()
    {
        return $this->actionRegisterClass;
    }

    /**
     * @param $actionRegisterClass
     */
    public function setActionRegisterClass($actionRegisterClass)
    {
        $this->actionRegisterClass = $actionRegisterClass;
    }
}