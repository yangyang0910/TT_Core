<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/8
 * Time: 上午11:02
 */

namespace Core\Component\Cluster\NetWork;


use Core\Component\Cluster\Bean\Command;
use Core\Component\Cluster\Bean\ClientNode;
use Core\Component\Cluster\Config;

class Deliver
{
    private $conf;

    function __construct(Config $applicationConfig)
    {
        $this->conf = $applicationConfig;
    }

    public function broadcast(Command $command)
    {
        foreach ($this->conf->getBroadcastAddress() as $address) {
            $address = explode(":", $address);
            Udp::broadcast((string)$this->commandCompleter($command), $address[1], $address[0]);
        }
    }

    public function send(Command $command, ClientNode $serverNode)
    {
        return Udp::sendTo((string)$this->commandCompleter($command), $serverNode->getListenPort(),
            $serverNode->getIp());
    }

    public function sendAndRec(Command $command, ClientNode $serverNode, $timeout = 0.5)
    {
        return Udp::sendAndRec((string)$this->commandCompleter($command), $serverNode->getListenPort(),
            $serverNode->getIp(), $timeout);
    }

    private function commandCompleter(Command $com)
    {
        $com->getFromNode()->setApplication($this->conf->getApplicationName());
        $com->getFromNode()->setNodeId($this->conf->getNodeId());
        $com->getFromNode()->setNodeName($this->conf->getNodeName());
        $com->getFromNode()->setBroadcastInterval($this->conf->getBroadcastInterval());
        $com->getFromNode()->setListenPort($this->conf->getListenPort());
        $com->setSignature($com->generateSignature($this->conf->getApplicationName(), $this->conf->getKey()));
        return $com;
    }
}