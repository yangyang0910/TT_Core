<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 下午4:21
 */

namespace Core\Component\RPC\Simple\AbstractInterface;


use Core\Component\RPC\Simple\Common\ActionList;

/**
 * Class AActionRegister
 * @package Core\Component\RPC\Simple\AbstractInterface
 */
abstract class AActionRegister
{
    /**
     * @param ActionList $actionList
     * @return mixed
     */
    abstract function register(ActionList $actionList);
}