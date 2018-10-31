<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/5
 * Time: 下午5:04
 */

namespace Core\Component\Cluster;

use Core\Component\Cluster\Bean\ClientNode;
use Core\Component\Cluster\Callback\BroadcastInterval;
use Core\Component\Cluster\Callback\ClusterRegister;
use Core\Component\Cluster\Callback\ClusterShutdown;
use Core\Component\Cluster\Callback\Command;
use Core\Component\Cluster\Bean\Command as CommandBean;
use Core\Component\Cluster\NetWork\Deliver;
use Core\Component\Cluster\NetWork\IpWhiteList;
use Core\Utility\Random;

class Config
{
    protected $applicationName;
    protected $nodeId;
    protected $nodeName;
    protected $listenPort;
    protected $listenAddress     = '0.0.0.0';
    protected $broadcastAddress  = [];
    protected $broadcastInterval = 15;
    protected $key;
    protected $startTime;
    protected $onCommand;
    protected $onBroadcast;
    protected $onApplicationRegister;
    protected $onClusterShutdown;
    protected $ipWhiteList;

    function __construct($applicationName)
    {
        $this->nodeId                = Random::character(8);
        $this->applicationName       = $applicationName;
        $this->onBroadcast           = new BroadcastInterval();
        $this->onCommand             = new Command();
        $this->ipWhiteList           = new IpWhiteList();
        $this->onApplicationRegister = new ClusterRegister();
        $this->onClusterShutdown     = new ClusterShutdown();
        $this->defaultHook();
    }

    /**
     * @return ClusterRegister
     */
    public function getOnApplicationRegister()
    {
        return $this->onApplicationRegister;
    }

    /**
     * @param ClusterRegister $onApplicationRegister
     * @return Config
     */
    public function setOnApplicationRegister(ClusterRegister $onApplicationRegister)
    {
        $this->onApplicationRegister = $onApplicationRegister;
        return $this;
    }

    /**
     * @return ClusterShutdown
     */
    public function getOnClusterShutdown()
    {
        return $this->onClusterShutdown;
    }

    /**
     * @param ClusterShutdown $onClusterShutdown
     */
    public function setOnClusterShutdown(ClusterShutdown $onClusterShutdown)
    {
        $this->onClusterShutdown = $onClusterShutdown;
    }


    function getApplicationName()
    {
        return $this->applicationName;
    }

    /**
     * @return mixed
     */
    public function getNodeId()
    {
        return $this->nodeId;
    }

    /*
     * 节点id最大长度8位
     */
    public function setNodeId($nodeId)
    {
        if (strlen($nodeId) > 8) {
            $nodeId = substr($nodeId, 0, 8);
        }
        $this->nodeId = $nodeId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNodeName()
    {
        return $this->nodeName;
    }


    public function setNodeName($nodeName)
    {
        $this->nodeName = $nodeName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getListenPort()
    {
        return $this->listenPort;
    }


    public function setListenPort($listenPort)
    {
        $this->listenPort = $listenPort;
        return $this;
    }

    /**
     * @return array
     */
    public function getBroadcastAddress()
    {
        return $this->broadcastAddress;
    }


    public function setBroadcastAddress(array $broadcastAddress)
    {
        $this->broadcastAddress = $broadcastAddress;
        return $this;
    }

    public function addBroadcastAddress($ip, $port)
    {
        array_push($this->broadcastAddress, "{$ip}:{$port}");
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStartTime()
    {
        return $this->startTime;
    }


    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
        return $this;
    }

    /**
     * @return int
     */
    public function getBroadcastInterval()
    {
        return $this->broadcastInterval;
    }

    public function setBroadcastInterval($broadcastInterval)
    {
        $this->broadcastInterval = $broadcastInterval;
        return $this;
    }

    /**
     * @return Command
     */
    public function getOnCommand()
    {
        return $this->onCommand;
    }

    public function setOnCommand(Command $onCommand)
    {
        $this->onCommand = $onCommand;
        return $this;
    }

    /**
     * @return BroadcastInterval
     */
    public function getOnBroadcast()
    {
        return $this->onBroadcast;
    }

    public function setOnBroadcast(BroadcastInterval $onBroadcast)
    {
        $this->onBroadcast = $onBroadcast;
        return $this;
    }

    /**
     * @return string
     */
    public function getListenAddress()
    {
        return $this->listenAddress;
    }

    public function setListenAddress($listenAddress)
    {
        $this->listenAddress = $listenAddress;
        return $this;
    }

    /**
     * @return IpWhiteList
     */
    public function getIpWhiteList()
    {
        return $this->ipWhiteList;
    }

    /**
     * @param IpWhiteList $ipWhiteList
     */
    public function setIpWhiteList(IpWhiteList $ipWhiteList)
    {
        $this->ipWhiteList = $ipWhiteList;
    }


    private function defaultHook()
    {
        /*
         * 每次广播周期，执行对外节点广播
         */
        $this->getOnBroadcast()->set('__NODE_BROADCAST__', function (Config $appConfig) {
            $com = new CommandBean();
            $com->setCommand('__NODE_BROADCAST__');
            (new Deliver($appConfig))->broadcast($com);
        });

        /*
         * 注册节点广播命令回调
         */
        $this->getOnCommand()->set('__NODE_BROADCAST__', function (\swoole_server $server, CommandBean $command) {
            //这里做多一次判断是为了避免，收到恶意包
            $manager = Cluster::getInstance()->getNodeManager($command->getFromNode()->getApplication());
            if ($manager) {
                $manager->refreshNode($command->getFromNode());
            }
        });

        $this->getOnCommand()->set('__NODE_OFFLINE__', function (\swoole_server $server, CommandBean $command) {
            $manager = Cluster::getInstance()->getNodeManager($command->getFromNode()->getApplication());
            if ($manager) {
                $manager->deleteNode($command->getFromNode()->getNodeId());
            }
        });

        /*
         * 每次当节点被注册的时候，
         */
        $this->getOnApplicationRegister()->set('__NODE_BROADCAST__', function (Config $appConfig) {
            //实例化自身节点信息并注册节点到本机节点
            $node = new ClientNode();
            $node->setIsLocal(1);
            $node->setIp('127.0.0.1');
            $node->setNodeName($appConfig->getNodeName());
            $node->setNodeId($appConfig->getNodeId());
            $node->setBroadcastInterval($appConfig->getBroadcastInterval());
            $node->setListenPort($appConfig->getListenPort());
            $node->setApplication($appConfig->getApplicationName());
            Cluster::getInstance()->getNodeManager($appConfig->getApplicationName())->refreshNode($node);
            //立即对外广播节点上线
            $appConfig->getOnBroadcast()->hook('__NODE_BROADCAST__', $appConfig);
        });

        /*
         * 注册服务关闭事件
         */
        $this->getOnClusterShutdown()->set('__NODE_OFFLINE__', function (Config $appConfig) {
            $com = new CommandBean();
            $com->setCommand('__NODE_OFFLINE__');
            (new Deliver($appConfig))->broadcast($com);
            //删除本机节点信息
            $manager = Cluster::getInstance()->getNodeManager($appConfig->getApplicationName());
            if ($manager) {
                $manager->deleteNode($appConfig->getNodeId());
            }
        });
    }

}