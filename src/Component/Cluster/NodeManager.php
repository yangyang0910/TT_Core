<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/13
 * Time: 下午8:16
 */

namespace Core\Component\Cluster;


use Core\Component\Cluster\Bean\ClientNode;

class NodeManager
{
    protected $swooleTable;

    function __construct()
    {
        /*
         * 每个application最多允许256个节点
         */
        $this->swooleTable = new \swoole_table(256);
        $this->swooleTable->column('nodeId', \swoole_table::TYPE_STRING, 8);
        $this->swooleTable->column('nodeName', \swoole_table::TYPE_STRING, 45);
        $this->swooleTable->column('application', \swoole_table::TYPE_STRING, 45);
        $this->swooleTable->column('ip', \swoole_table::TYPE_STRING, 15);
        $this->swooleTable->column('listenPort', \swoole_table::TYPE_INT, 8);
        $this->swooleTable->column('isLocal', \swoole_table::TYPE_INT, 1);
        $this->swooleTable->column('broadcastInterval', \swoole_table::TYPE_INT, 4);
        $this->swooleTable->column('lastHeartBeat', \swoole_table::TYPE_INT, 8);
        $this->swooleTable->create();
    }

    function refreshNode(ClientNode $clientNode)
    {
        if ($this->nodeClearCheck($clientNode->getNodeId(), $clientNode->getLastHeartBeat(),
            $clientNode->getBroadcastInterval(), $clientNode->getIsLocal())) {
            $this->swooleTable->set($clientNode->getNodeId(), $clientNode->toArray());
        }
        return $this;
    }

    function allNodes()
    {
        $list = [];
        foreach ($this->swooleTable as $key => $nodeArr) {
            if ($this->nodeClearCheck($key, $nodeArr['lastHeartBeat'], $nodeArr['broadcastInterval'],
                $nodeArr['isLocal'])) {
                array_push($list, new ClientNode($nodeArr));
            }
        }
        return $list;
    }

    function getNode($nodeId)
    {
        $nodeArr = $this->swooleTable->get($nodeId);
        if (is_array($nodeArr)) {
            if ($this->nodeClearCheck($nodeId, $nodeArr['lastHeartBeat'], $nodeArr['broadcastInterval'],
                $nodeArr['isLocal'])) {
                return new ClientNode($nodeArr);
            }
        }
        return null;
    }

    function deleteNode($nodeId)
    {
        $this->swooleTable->del($nodeId);
        return $this;
    }

    /*
     * 节点过期返回false
     */
    private function nodeClearCheck($nodeId, $lastHeartBeat, $broadcastInterval, $isLocal)
    {
        if ((time() - $lastHeartBeat > $broadcastInterval) && !$isLocal) {
            $this->swooleTable->del($nodeId);
            return false;
        } else {
            return true;
        }
    }
}