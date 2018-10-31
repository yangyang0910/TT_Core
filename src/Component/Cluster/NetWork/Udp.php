<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/7
 * Time: 下午1:02
 */

namespace Core\Component\Cluster\NetWork;


use Core\Component\Error\Trigger;

class Udp
{
    static function broadcast($str, $port, $address = '255.255.255.255')
    {
        if (!($sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
            $errorcode = socket_last_error();
            $errormsg  = socket_strerror($errorcode);
            Trigger::error($errormsg);
            return false;
        } else {
            var_dump($sock);
            socket_set_option($sock, 65535, SO_BROADCAST, true);
            socket_sendto($sock, $str, strlen($str), 0, $address, $port);
            socket_close($sock);
            return true;
        }
    }

    /*
     * 等待用携程客户端重写
     */
    static function sendTo($str, $port, $address)
    {
        $client = new \Swoole\Coroutine\Client(SWOOLE_SOCK_UDP);
        if ($client->connect($address, $port)) {
            $res = (bool)$client->send($str);
            unset($client);
            return $res;
        } else {
            return false;
        }
    }

    static function sendAndRec($str, $port, $address, $timeout = 0.5)
    {
        $client = new \Swoole\Coroutine\Client(SWOOLE_SOCK_UDP);
        if ($client->connect($address, $port)) {
            $client->send($str);
            $res = $client->recv($timeout);
            unset($client);
            return $res;
        } else {
            return null;
        }
    }
}