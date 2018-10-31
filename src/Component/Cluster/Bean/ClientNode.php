<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/6
 * Time: 下午10:40
 */

namespace Core\Component\Cluster\Bean;


use Core\Component\Spl\SplBean;

class ClientNode extends SplBean
{
    protected $nodeId;
    protected $application       = '';
    protected $nodeName;
    protected $ip;
    protected $lastHeartBeat;
    protected $isLocal           = 0;
    protected $broadcastInterval = 5;
    protected $listenPort;

    /**
     * @return string
     */
    public function getApplication()
    {
        return $this->application;
    }

    public function setApplication($application)
    {
        $this->application = $application;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNodeId()
    {
        return $this->nodeId;
    }

    public function setNodeId($nodeId)
    {
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
    public function getIp()
    {
        return $this->ip;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastHeartBeat()
    {
        return $this->lastHeartBeat;
    }

    public function setLastHeartBeat($lastHeartBeat)
    {
        $this->lastHeartBeat = $lastHeartBeat;
        return $this;
    }

    /**
     * @return int
     */
    public function getIsLocal()
    {
        return $this->isLocal;
    }


    public function setIsLocal($isLocal)
    {
        $this->isLocal = $isLocal;
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
     * @return int
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

    protected function initialize()
    {
        if (empty($this->lastHeartBeat)) {
            $this->lastHeartBeat = time();
        }
    }
}