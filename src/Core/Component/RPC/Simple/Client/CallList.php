<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 下午5:38
 */

namespace Core\Component\RPC\Simple\Client;


use Core\Component\RPC\Simple\Common\Package;

/**
 * Class CallList
 * @package Core\Component\RPC\Simple\Client
 */
class CallList
{
    /**
     * @var array
     */
    private $taskList = [];

    /**
     * @param               $serverName
     * @param               $action
     * @param array|NULL    $args
     * @param callable|NULL $successCall
     * @param callable|NULL $failCall
     * @return $this
     */
    function addCall($serverName, $action, array $args = null, callable $successCall = null, callable $failCall = null)
    {
        $package = new Package();
        $package->setServerName($serverName);
        $package->setAction($action);
        $package->setArgs($args);
        $this->taskList[] = new Call($package, $successCall, $failCall);
        return $this;
    }

    /**
     * @return array
     */
    function getTaskList()
    {
        return $this->taskList;
    }
}