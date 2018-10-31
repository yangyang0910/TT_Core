<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 下午3:45
 */

namespace Core\Component\RPC\Simple\AbstractInterface;


use Core\Component\RPC\Simple\Common\Package;
use Core\Component\Socket\Client\TcpClient;

/**
 * Class APackageParser
 * @package Core\Component\RPC\Simple\AbstractInterface
 */
abstract class APackageParser
{
    /**
     * @param Package   $result
     * @param TcpClient $client
     * @param           $rawData
     * @return mixed
     */
    abstract function decode(Package $result, TcpClient $client, $rawData);

    /**
     * @param Package $res
     * @return string
     */
    abstract function encode(Package $res);
}