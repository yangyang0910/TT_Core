<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/27
 * Time: 下午5:51
 */

namespace Core\Component\RPC\Standard;


use Core\Component\Openssl;
use Core\Component\RPC\Standard\AbstractInterface\AbstractService;
use Core\Component\RPC\Standard\Bean\Caller;
use Core\Component\RPC\Standard\Bean\IpWhiteList;
use Core\Component\RPC\Standard\Bean\Response;
use Core\Component\RPC\Standard\Bean\ServiceNode;
use Core\Component\RPC\Standard\Client\Client;
use Core\Component\RPC\Standard\Bean\Client as ClientInfo;
use Core\Component\Error\Trigger;
use Swoole\Timer;

class Rpc
{
    /**
     * @var Trigger
     */
    private $trigger;
    private $serviceList = [];
    private $openssl     = null;
    private $swooleTable;
    private $config;
    private $serverPort  = null;

    function __construct(Config $config, Trigger $trigger)
    {
        $this->trigger     = $trigger;
        $this->config      = $config;
        $this->swooleTable = new \swoole_table($this->config->getMaxNodes());
        $this->swooleTable->column('serviceName', \swoole_table::TYPE_STRING, 45);
        $this->swooleTable->column('serviceId', \swoole_table::TYPE_STRING, 8);
        $this->swooleTable->column('ip', \swoole_table::TYPE_STRING, 15);
        $this->swooleTable->column('port', \swoole_table::TYPE_STRING, 5);
        $this->swooleTable->column('lastHeartBeat', \swoole_table::TYPE_INT, 8);
        $this->swooleTable->create();
    }

    /*
     * 注册一个tcp服务作为RPC通讯服务
     */
    function attach(\swoole_server $server)
    {
        if ($this->config->isSubServerMode()) {
            $subPort          = $server->addListener($this->config->getListenHost(), $this->config->getServicePort(), SWOOLE_TCP);
            $this->serverPort = $this->config->getServicePort();
        } else {
            $this->serverPort = $server->port;
            $subPort          = $server;
        }
        /*
         * 配置包结构
         */
        $subPort->set(
            [
                'open_length_check'        => true,
                'package_length_type'      => 'N',
                'package_length_offset'    => 0,
                'package_body_offset'      => 4,
                'package_max_length'       => $this->config->getMaxPackage(),
                'heartbeat_idle_time'      => $this->config->getHeartbeatIdleTime(),
                'heartbeat_check_interval' => $this->config->getHeartbeatCheckInterval(),
            ]
        );
        //是否启用数据包加密
        if (!empty($this->config->getSecretKey())) {
            $this->openssl = new Openssl($this->config->getSecretKey());
        }
        //注册 onReceive 回调
        $subPort->on('receive', function (\swoole_server $server, $fd, $reactor_id, $data) {
            $info = $server->connection_info($fd);
            //这里做ip白名单过滤
            if ($this->config->getIpWhiteList() instanceof IpWhiteList) {
                if (!$this->config->getIpWhiteList()->check($info['remote_ip'])) {
                    $server->close($fd);
                    return;
                }
            }
            $data = self::dataUnPack($data);
            if ($this->openssl instanceof Openssl) {
                $data = $this->openssl->decrypt($data);
            }
            $data   = json_decode($data, true);
            $client = new ClientInfo();
            $client->setFd($fd);
            $client->setReactorId($reactor_id);
            $client->setIp($info['remote_ip']);
            $response = new Response();
            if (is_array($data)) {
                $caller = new Caller($data);
                $caller->setClient($client);
                if (isset($this->serviceList[$caller->getService()])) {
                    $service = $this->serviceList[$caller->getService()];
                    try {
                        (new $service($caller, $response, $this->trigger));
                    } catch (\Throwable $throwable) {
                        $this->trigger->exception($throwable);
                    }
                } else {
                    $response->setStatus(Response::STATUS_SERVICE_NOT_FOUND);
                }
                //响应分离的时候，不回复任何消息，也不断开连接，该场景用于异步
                if ($response->getStatus() == Response::STATUS_RESPONSE_DETACH) {
                    return;
                }
            } else {
                $response->setStatus(Response::STATUS_PACKAGE_ERROR);
            }
            $res = $response->__toString();
            if ($this->openssl instanceof Openssl) {
                $res = $this->openssl->encrypt($res);
            }
            $res = self::dataPack($res);
            $server->send($fd, $res);
            //短链接
            $server->close($fd);
        });
        /*
         * 如果配置了服务广播
         */
        if ($this->config->isEnableBroadcast()) {
            $broadcast = $server->addListener($this->config->getListenHost(), $this->config->getBroadcastListenPort(), SWOOLE_UDP);
            $broadcast->on('packet', function (\swoole_server $server, $data, array $client_info) {
                //这里做ip白名单过滤
                if ($this->config->getIpWhiteList() instanceof IpWhiteList) {
                    if (!$this->config->getIpWhiteList()->check($client_info['address'])) {
                        return;
                    }
                }
                if ($this->openssl instanceof Openssl) {
                    $data = $this->openssl->decrypt($data);
                }
                $json = json_decode($data, true);
                if (is_array($json)) {
                    $node = new ServiceNode($json);
                    $node->setIp($client_info['address']);
                    //刷新节点
                    $this->refreshServiceNode($node);
                }
            });
            //添加自定义进程做定时广播
            $server->addProcess(new \swoole_process(function (\swoole_process $process) {
                //服务正常关闭的时候，对外广播服务下线
                $process::signal(SIGTERM, function () use ($process) {
                    swoole_event_del($process->pipe);
                    $this->broadcastAllService(0);
                    $process->exit(0);
                });
                swoole_event_add($process->pipe, function () use ($process) {
                    $process->read(64 * 1024);
                });
                //服务启动后立即广播服务发现
                Timer::after(500, function () {
                    $this->broadcastAllService(time());
                });
                //默认5秒广播一次服务发现
                Timer::tick(5000, function () {
                    $this->broadcastAllService(time());
                });
            }));
        }
    }

    /*
     * 刷新/注册一个服务节点
     */
    function refreshServiceNode(ServiceNode $serviceNode)
    {
        $this->swooleTable->set(substr(md5($serviceNode->getServiceId() . $serviceNode->getServiceName()), 8, 16),
            $serviceNode->toArray());
        if ($this->config->isEnableBroadcast()) {
            $this->gcServiceNodes();
        }
    }

    /*
     * 获取全部服务节点
     */
    function getAllServiceNodes()
    {
        $res = [];
        foreach ($this->swooleTable as $item) {
            array_push($res, new ServiceNode($item));
        }
        return $res;
    }

    /*
     * 获取某个服务的全部节点
     */
    function getServiceNodes($serviceName)
    {
        $res = [];
        foreach ($this->swooleTable as $item) {
            if ($item['serviceName'] == $serviceName) {
                array_push($res, new ServiceNode($item));
            }
        }
        return $res;
    }

    /*
     * 获取某个服务的任意一个节点
     */
    function getServiceNode($serviceName)
    {
        $list = $this->getServiceNodes($serviceName);
        if (!empty($list)) {
            mt_srand();
            $data = $this->getServiceNodes($serviceName);
            return $data[mt_rand(0, count($data) - 1)];
        } else {
            return null;
        }
    }

    private function gcServiceNodes()
    {
        foreach ($this->swooleTable as $key => $item) {
            if (time() - $item['lastHeartBeat'] > 10) {
                $this->swooleTable->del($key);
            }
        }
    }
    /*
     * 注册一个服务控制器
     */
    /**
     * @param string $serviceName
     * @param string $serviceClass
     * @return $this
     * @throws \Exception
     */
    function registerService($serviceName, $serviceClass)
    {
        if (!isset($this->serviceList[$serviceName])) {
            $ref = new \ReflectionClass($serviceClass);
            if ($ref->isSubclassOf(AbstractService::class)) {
                $this->serviceList[$serviceName] = $serviceClass;
            } else {
                throw new \Exception("class {$serviceClass} is not a Rpc Service class");
            }
        }
        return $this;
    }

    /*
     * 获取一个客户端
     */
    function client()
    {
        return new Client($this->config, $this, $this->trigger);
    }

    public static function dataPack($sendStr)
    {
        return pack('N', strlen($sendStr)) . $sendStr;
    }

    public static function dataUnPack($rawData)
    {
        $len  = unpack('N', $rawData);
        $data = substr($rawData, '4');
        if (strlen($data) != $len[1]) {
            return null;
        } else {
            return $data;
        }
    }

    /**
     * @return null
     */
    public function getServerPort()
    {
        return $this->serverPort;
    }

    private function broadcast($msg, $addr, $port)
    {
        if ($this->openssl instanceof Openssl) {
            $msg = $this->openssl->encrypt($msg);
        }
        if (!($sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
            $errorcode = socket_last_error();
            $errormsg  = socket_strerror($errorcode);
            $this->trigger->error($errormsg);
        } else {
            socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, true);
            socket_sendto($sock, $msg, strlen($msg), 0, $addr, $port);
            socket_close($sock);
        }
    }

    private function broadcastAllService($time)
    {
        foreach ($this->serviceList as $serviceName => $serviceClass) {
            $node = new ServiceNode();
            $node->setServiceName($serviceName);
            $node->setPort($this->serverPort);
            $node->setServiceId($this->config->getServiceId());
            //时间正确为上线,0为下线
            $node->setLastHeartBeat($time);
            $msg = $node->__toString();
            foreach ($this->config->getBroadcastList()->getList() as $address) {
                var_dump($address);
                $address = explode(':', $address);
                $this->broadcast($msg, $address[0], $address[1]);
            }
        }
    }
}