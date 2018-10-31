<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/28
 * Time: 下午2:55
 */

namespace Core\Component\RPC\Standard;


use Core\Component\RPC\Standard\Bean\BroadcastList;
use Core\Component\RPC\Standard\Bean\IpWhiteList;
use Core\Utility\Random;

class Config
{
    private $servicePort              = 9601;
    private $serviceId;
    private $listenHost               = '0.0.0.0';
    private $subServerMode            = true;
    private $enableBroadcast          = false;
    private $broadcastListenPort      = 9602;
    private $broadcastList            = null;
    private $maxNodes                 = 2048;
    private $maxPackage               = 1024 * 64;
    private $secretKey                = '';
    private $heartbeat_idle_time      = 30;
    private $heartbeat_check_interval = 5;
    private $ipWhiteList              = null;

    function __construct()
    {
        $this->serviceId = Random::character(8);
    }

    /**
     * @return IpWhiteList
     */
    function getIpWhiteList()
    {
        return $this->ipWhiteList;
    }

    function setIpWhiteList()
    {
        if (empty($this->ipWhiteList)) {
            $this->ipWhiteList = new IpWhiteList();
        }
        return $this->ipWhiteList;
    }

    /**
     * @return int
     */
    public function getServicePort()
    {
        return $this->servicePort;
    }

    /**
     * @param int $servicePort
     */
    public function setServicePort($servicePort)
    {
        $this->servicePort = $servicePort;
    }

    /**
     * @return bool
     */
    public function isSubServerMode()
    {
        return $this->subServerMode;
    }

    /**
     * @param bool $subServerMode
     */
    public function setSubServerMode($subServerMode)
    {
        $this->subServerMode = $subServerMode;
    }

    /**
     * @return bool
     */
    public function isEnableBroadcast()
    {
        return $this->enableBroadcast;
    }

    /**
     * @param bool $enableBroadcast
     */
    public function setEnableBroadcast($enableBroadcast)
    {
        $this->enableBroadcast = $enableBroadcast;
    }

    /**
     * @return int
     */
    public function getBroadcastListenPort()
    {
        return $this->broadcastListenPort;
    }

    /**
     * @param int $broadcastListenPort
     */
    public function setBroadcastListenPort($broadcastListenPort)
    {
        $this->broadcastListenPort = $broadcastListenPort;
    }

    public function getBroadcastList()
    {
        if (!isset($this->broadcastList)) {
            $this->broadcastList = new BroadcastList();
        }
        return $this->broadcastList;
    }

    /**
     * @param $broadcastList
     */
    public function setBroadcastList(BroadcastList $broadcastList)
    {
        $this->broadcastList = $broadcastList;
    }

    /**
     * @return int
     */
    public function getMaxNodes()
    {
        return $this->maxNodes;
    }

    /**
     * @param int $maxNodes
     */
    public function setMaxNodes($maxNodes)
    {
        $this->maxNodes = $maxNodes;
    }

    /**
     * @return string
     */
    public function getListenHost()
    {
        return $this->listenHost;
    }

    /**
     * @param string $listenHost
     */
    public function setListenHost($listenHost)
    {
        $this->listenHost = $listenHost;
    }

    /**
     * @return float|int
     */
    public function getMaxPackage()
    {
        return $this->maxPackage;
    }

    /**
     * @param float|int $maxPackage
     */
    public function setMaxPackage($maxPackage)
    {
        $this->maxPackage = $maxPackage;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @param string $secretKey
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * @return int
     */
    public function getHeartbeatIdleTime()
    {
        return $this->heartbeat_idle_time;
    }

    /**
     * @param int $heartbeat_idle_time
     */
    public function setHeartbeatIdleTime($heartbeat_idle_time)
    {
        $this->heartbeat_idle_time = $heartbeat_idle_time;
    }

    /**
     * @return int
     */
    public function getHeartbeatCheckInterval()
    {
        return $this->heartbeat_check_interval;
    }

    /**
     * @param int $heartbeat_check_interval
     */
    public function setHeartbeatCheckInterval($heartbeat_check_interval)
    {
        $this->heartbeat_check_interval = $heartbeat_check_interval;
    }

    /**
     * @return mixed
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @param mixed $serviceId
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;
    }
}