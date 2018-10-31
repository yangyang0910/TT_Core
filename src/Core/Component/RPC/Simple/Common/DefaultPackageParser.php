<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 下午4:15
 */

namespace Core\Component\RPC\Simple\Common;


use Core\Component\RPC\Simple\AbstractInterface\APackageParser;
use Core\Component\Socket\Client\TcpClient;

/**
 * Class DefaultPackageParser
 * @package Core\Component\RPC\Simple\Common
 */
class DefaultPackageParser extends APackageParser
{
    /**
     * @param Package   $result
     * @param TcpClient $client
     * @param           $rawData
     * @return mixed|void
     */
    function decode(Package $result, TcpClient $client, $rawData)
    {
        $len  = unpack('N', $rawData);
        $data = substr($rawData, 4);
        if (strlen($data) != $len[1]) {
            return null;
        } else {
            $data = json_decode($data, 1);
            if (0 >= $data) {
                return null;
            }
            $result->setServerName($data['serverName']);
            $result->setAction($data['action']);
            $result->setArgs($data['args']);
            $result->setMessage($data['message']);
            $result->setErrorCode($data['errorCode']);
            $result->setErrorMsg($data['errorMsg']);
        }
    }

    /**
     * @param Package $res
     * @return string
     */
    function encode(Package $res)
    {
        $data = $res->__toString();
        return pack('N', strlen($data)) . $data;
    }
//
//    /**
//     * @param Package   $result
//     * @param TcpClient $client
//     * @param           $rawData
//     * @return mixed|void
//     */
//    function decode(Package $result, TcpClient $client, $rawData)
//    {
//        $rawData = pack('H*', base_convert($rawData, 2, 16));
//        $js      = json_decode($rawData, 1);
//        $js      = is_array($js) ? $js : [];
//        $result->arrayToBean($js);
//    }
//
//    /**
//     * @param Package $res
//     * @return string
//     */
//    function encode(Package $res)
//    {
//        $data  = $res->__toString();
//        $value = unpack('H*', $data);
//        return $value[1];
//    }
}