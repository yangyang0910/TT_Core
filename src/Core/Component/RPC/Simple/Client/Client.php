<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 下午5:35
 */

namespace Core\Component\RPC\Simple\Client;


use Core\Component\RPC\Simple\AbstractInterface\APackageParser;
use Core\Component\RPC\Simple\Common\Config;
use Core\Component\RPC\Simple\Common\Package;
use Core\Component\Socket\Client\TcpClient;

/**
 * Class Client
 * @package Core\Component\RPC\Simple\Client
 */
class Client
{
    /**
     * @var array
     */
    private $serverList = [];
    /**
     * @var array
     */
    private $serverConf = [];

    /**
     * @param Config $conf
     * @return CallList|mixed
     * @throws \Exception
     */
    function selectServer(Config $conf)
    {
        if (empty($conf->getHost())) {
            throw new \Exception("rpc host error @" . $conf->getHost());
        }
        if (empty($conf->getPort())) {
            throw new \Exception("rpc host port error @" . $conf->getPort());
        }
        if (empty($conf->getPackageParserClass())) {
            throw new \Exception("rpc packageParserClass  error @" . $conf->getPort());
        }
        $serverHash = spl_object_hash($conf);
        if (isset($this->serverList[$serverHash])) {
            return $this->serverList[$serverHash];
        } else {
            $call                          = new CallList();
            $this->serverList[$serverHash] = $call;
            $this->serverConf[$serverHash] = $conf;
            return $call;
        }
    }

    /**
     * @param int $timeOut
     */
    function call($timeOut = 1000)
    {
        $clients             = [];
        $clientsInfo         = [];
        $serverPackageParser = [];
        foreach ($this->serverList as $serverHash => $callList) {
            /** @var Config $serverConf */
            $serverConf = $this->serverConf[$serverHash];
            /** @var CallList $callList */
            $currentTaskList            = $callList->getTaskList();
            $currentServerPackageParser = $serverConf->getPackageParserClass();
            if (class_exists($currentServerPackageParser)) {
                $serverPackageParser[$serverHash] = $currentServerPackageParser = new $currentServerPackageParser();
            }
            foreach ($currentTaskList as $task) {
                /** @var Call $task */
                if ($task instanceof Call) {
                    $client = new \swoole_client(SWOOLE_TCP, SWOOLE_SOCK_SYNC);
                    $client->set([
                        'open_eof_check' => true,
                        'package_eof'    => $serverConf->getEof(),//\r\n
                    ]);
                    $client->connect($serverConf->getHost(), $serverConf->getPort(), $serverConf->getConnectTimeOut(),
                        0);
                    if ($client->isConnected()) {
                        if ($currentServerPackageParser instanceof APackageParser) {
                            $data = $currentServerPackageParser->encode($task->getPackage());
                            $client->send($data . $serverConf->getEof());
                            $clients[$client->sock]     = $client;
                            $clientsInfo[$client->sock] = [
                                'callObj'    => $task,
                                'eof'        => $serverConf->getEof(),
                                'serverHash' => $serverHash,
                            ];
                        }
                    } else {
                        $handler = $task->getFailCall();
                        //失败的时候立即执行失败回调
                        if (is_callable($handler)) {
                            $res = new Package();
                            $res->setErrorCode($res::ERROR_SERVER_CONNECT_FAIL);
                            call_user_func_array($handler, [
                                $task->getPackage(),
                                $res,
                            ]);
                        }
                    }
                }
            }
        }
        $start = microtime(1);
        while (!empty($clients)) {
            $write = $error = [];
            $read  = array_values($clients);
            $n     = swoole_client_select($read, $write, $error, 0.1);
            if ($n > 0) {
                /** @var \swoole_client $c */
                foreach ($read as $index => $c) {
                    $data       = $c->recv();
                    $eof        = $clientsInfo[$c->sock]['eof'];
                    $data       = substr($data, 0, -strlen($eof));
                    $serverHash = $clientsInfo[$c->sock]['serverHash'];
                    $decoder    = $serverPackageParser[$serverHash];
                    $res        = new Package();
                    if ($decoder instanceof APackageParser) {
                        $decoder->decode($res, new TcpClient(), $data);
                    }
                    /** @var Call $call */
                    $call = $clientsInfo[$c->sock]['callObj'];
                    if ($res->getErrorCode() || $res->getErrorMsg()) {
                        $handler = $call->getFailCall();
                        if (is_callable($handler)) {
                            call_user_func_array($handler, [
                                $call->getPackage(),
                                $res,
                            ]);
                        }
                    } else {
                        $handler = $call->getSuccessCall();
                        if (is_callable($handler)) {
                            call_user_func_array($handler, [
                                $call->getPackage(),
                                $res,
                            ]);
                        }
                    }
                    $c->close();
                    unset($clients[$c->sock]);
                    unset($clientsInfo[$c->sock]);
                }
            }
            $now   = microtime(1);
            $spend = intval(($now - $start) * 1000);
            if ($spend > $timeOut) {
                foreach ($clients as $sock => $client) {
                    /** @var Call $call */
                    $call    = $clientsInfo[$sock]['callObj'];
                    $handler = $call->getSuccessCall();
                    if (is_callable($handler)) {
                        $res = new Package();
                        $res->setErrorCode($res::ERROR_SERVER_RESPONSE_TIME_OUT);
                        call_user_func_array($handler, [
                            $call->getPackage(),
                            $res,
                        ]);
                    }
                    $client->close();
                    unset($clients[$client->sock]);
                    unset($clientsInfo[$client->sock]);
                }
                break;
            }
        }
    }
}