<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 下午1:46
 */

namespace Core\Swoole\Pipe;


use Core\Swoole\Server;

/**
 * Class Send
 * @package Core\Swoole\Pipe
 */
class Send
{
    /**
     * @param Message $message
     * @param         $workerId
     * @return bool
     */
    static function send(Message $message, $workerId)
    {
        return Server::getInstance()->getServer()->sendMessage($message->__toString(), $workerId);
    }
}