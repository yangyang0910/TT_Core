<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 下午3:46
 */

namespace Core\Component\RPC\Simple\Common;


/**
 * Class ActionList
 * @package Core\Component\RPC\Simple\Common
 */
class ActionList
{
    /**
     * @var array
     */
    private $list = [];

    /**
     * @param          $name
     * @param callable $call
     */
    function registerAction($name, callable $call)
    {
        $this->list[$name] = $call;
    }

    /**
     * @param callable $call
     */
    function setDefaultAction(callable $call)
    {
        $this->list['__DEFAULT__'] = $call;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    function getHandler($name)
    {
        if (isset($this->list[$name])) {
            return $this->list[$name];
        } else {
            return isset($this->list['__DEFAULT__']) ? $this->list['__DEFAULT__'] : null;
        }
    }
}