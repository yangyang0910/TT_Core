<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 上午2:38
 */

namespace Core\Swoole\Pipe;


/**
 * Class ACommandRegister
 * @package Core\Swoole\Pipe
 */
abstract class ACommandRegister
{
    /**
     * @param CommandList $commandList
     * @return mixed
     */
    abstract function register(CommandList $commandList);
}