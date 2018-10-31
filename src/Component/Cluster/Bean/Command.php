<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/7
 * Time: 下午12:49
 */

namespace Core\Component\Cluster\Bean;


use Core\Component\Spl\SplBean;

class Command extends SplBean
{
    protected $command;
    protected $args = [];
    protected $fromNode;
    protected $signature;
    protected $time;

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param mixed $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param array $args
     */
    public function setArgs(array $args)
    {
        $this->args = $args;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param mixed $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * @return mixed
     */
    public function getFromNode()
    {
        return $this->fromNode;
    }

    /**
     * @param mixed $fromNode
     */
    public function setFromNode(ClientNode $fromNode)
    {
        $this->fromNode = $fromNode;
    }


    /**
     * @param mixed $signature
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
    }


    protected function initialize()
    {
        if (empty($this->time)) {
            $this->time = time();
        }
        if (is_array($this->fromNode)) {
            $this->fromNode = new ClientNode($this->fromNode);
        } else {
            $this->fromNode = new ClientNode();
        }
    }

    function generateSignature($appName, $appKey)
    {
        $data = $this->args;
        ksort($data);
        return md5(json_encode($data, true) . $this->time . $this->command . $appName . $appKey);
    }

    function verifySignature($appName, $appKey)
    {
        if ($this->signature === $this->generateSignature($appName, $appKey)) {
            return true;
        } else {
            return false;
        }
    }
}