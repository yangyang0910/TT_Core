<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/5
 * Time: 下午5:02
 */

namespace Core\Component\Cluster;

use Core\Component\Cluster\Bean\Command;
use Core\Component\Cluster\Exception\ApplicationShutdown;
use Core\Component\Cluster\Exception\BroadcastCall;
use Core\Component\Cluster\Exception\ApplicationRegister;
use Core\Component\Cluster\Exception\CommandCall;
use Core\Component\Cluster\Exception\ConfigEmpty;
use Core\Component\Cluster\Exception\PortOccupied;
use Core\AbstractInterface\TSingleton;
use Core\Component\Error\Trigger;
use Swoole\Process;


class Cluster
{
    use TSingleton;

    protected $nodeManager  = [];
    protected $applications = [];

    /**
     * @param $appName
     * @return Config|mixed
     */
    public function application($appName)
    {
        if (isset($this->applications[$appName])) {
            return $this->applications[$appName];
        } else {
            $conf                         = new Config($appName);
            $this->applications[$appName] = $conf;
            $this->nodeManager[$appName]  = new NodeManager();
            return $conf;
        }
    }

    /**
     * @param $appName
     * @return mixed|null
     */
    public function getNodeManager($appName)
    {
        if (isset($this->nodeManager[$appName])) {
            return $this->nodeManager[$appName];
        } else {
            return null;
        }
    }

    /*
     * 启动过程中，任何异常都直接抛出，请在外部捕获,此处实现不依赖 EasySwoole Core组件包，让用户最低成本使用本组件
     */
    /**
     * @param \swoole_server $server
     * @throws ApplicationRegister
     * @throws ConfigEmpty
     * @throws PortOccupied
     */
    public function attach(\swoole_server $server)
    {
        $intervalBroadcastTasks = [];
        $clusterShutdownCalls   = [];
        foreach ($this->applications as $application => $applicationConfig) {
            /** @var Config $applicationConfig */
            $this->configCheck($application, $applicationConfig);
            $sub_server = $server->addListener($applicationConfig->getListenAddress(), $applicationConfig->getListenPort(), SWOOLE_SOCK_UDP);
            if ($sub_server instanceof \Swoole\Server\Port) {
                $broadcastInterval = $applicationConfig->getBroadcastInterval();
                if (!isset($intervalBroadcastTasks[$broadcastInterval])) {
                    $intervalBroadcastTasks[$broadcastInterval] = [];
                }
                array_push($intervalBroadcastTasks[$broadcastInterval], [
                    'applicationConfig' => $applicationConfig,
                    'call'              => $applicationConfig->getOnBroadcast(),
                ]);
                array_push($clusterShutdownCalls, [
                    'applicationConfig' => $applicationConfig,
                    'call'              => $applicationConfig->getOnClusterShutdown(),
                ]);
                $sub_server->on('packet', function (\swoole_server $serv, $data, $addr) use ($applicationConfig) {
                    if (!$applicationConfig->getIpWhiteList()->filter($addr['address'])) {
                        return;
                    }
                    $json = json_decode($data, true);
                    if (is_array($json)) {
                        $com = new Command($json);
                        /*
                         * 每个命令在3s内有效
                         */
                        if ($com->verifySignature($applicationConfig->getApplicationName(),
                                $applicationConfig->getKey()) && (time() - $com->getTime() < 3)) {
                            $com->getFromNode()->setIp($addr['address']);
                            $call = $applicationConfig->getOnCommand()->get($com->getCommand());
                            if (is_callable($call)) {
                                try {
                                    $res = call_user_func($call, $serv, $com, $addr);
                                    if (is_string($res)) {
                                        $serv->sendto($addr['address'], $addr['port'], $res, $addr['server_socket']);
                                    }
                                } catch (\Throwable $throwable) {
                                    Trigger::exception(new CommandCall("cluster application '{$applicationConfig->getApplicationName()}' error : {$throwable->getMessage()}"));
                                }
                            }
                        }
                    }
                });
                $clusterRegisterCalls = $applicationConfig->getOnApplicationRegister()->all();
                foreach ($clusterRegisterCalls as $callName => $clusterRegisterCall) {
                    try {
                        if ($clusterRegisterCall instanceof \Closure) {
                            call_user_func($clusterRegisterCall, $applicationConfig);
                        }
                    } catch (\Throwable $throwable) {
                        throw new ApplicationRegister("cluster application '{$application}' call onClusterRegister event '{$callName}' fail:{$throwable->getMessage()}");
                    }
                }
            } else {
                throw new PortOccupied("cluster application '{$application}' bind({$applicationConfig->getListenAddress()}:{$applicationConfig->getListenPort()}) fail");
            }
        }
        $process = new Process(function (Process $process) use (
            $server,
            $intervalBroadcastTasks,
            $clusterShutdownCalls
        ) {
            pcntl_async_signals(true);
            Process::signal(SIGTERM, function () use ($process, $clusterShutdownCalls) {
                swoole_event_del($process->pipe);
                foreach ($clusterShutdownCalls as $clusterShutdownCallArray) {
                    $calls = $clusterShutdownCallArray['call']->all();
                    /** @var Config $applicationConfig */
                    $applicationConfig = $clusterShutdownCallArray['applicationConfig'];
                    foreach ($calls as $callName => $call) {
                        try {
                            call_user_func($call, $clusterShutdownCallArray['applicationConfig']);
                        } catch (\Throwable $throwable) {
                            Trigger::exception(new ApplicationShutdown("cluster application '{$applicationConfig->getApplicationName()}' shutdown call '{$callName}' fail : {$throwable->getMessage()}"));
                        }
                    }
                }

                $process->exit(0);
            });
            swoole_event_add($process->pipe, function () use ($process) {
                $process->read(64 * 1024);
            });
            /*
             * 定时执行广播任务，因为可能两个不同的应用，都具有相同的定时周期
             */
            foreach ($intervalBroadcastTasks as $interval => $intervalBroadcastTaskArray) {
                $server->tick($interval * 1000, function () use ($intervalBroadcastTaskArray) {
                    foreach ($intervalBroadcastTaskArray as $intervalBroadcastTask) {
                        $all = $intervalBroadcastTask['call']->all();
                        foreach ($all as $event => $call) {
                            try {
                                if (current($call) instanceof \Closure) {
                                    call_user_func(current($call), $intervalBroadcastTask['applicationConfig']);
                                }
                            } catch (\Throwable $throwable) {
                                $appName = $intervalBroadcastTask['applicationConfig']->getApplicationName();
                                Trigger::exception(new BroadcastCall("cluster application '{$appName}' BroadcastCall {$event} error : {$throwable->getMessage()}"));
                            }
                        }
                    }
                });
            }
        });
        $server->addProcess($process);
    }

    /**
     * @param        $application
     * @param Config $config
     * @throws ConfigEmpty
     */
    private function configCheck($application, Config $config)
    {
        $checks = ['nodeName', 'nodeId', 'key', 'listenPort', 'listenAddress', 'broadcastAddress', 'broadcastInterval'];
        foreach ($checks as $itemName) {
            $method = 'get' . ucfirst($itemName);
            if (empty($config->$method())) {
                throw new ConfigEmpty("cluster application  '{$application}' can not start with empty '{$itemName}' config");
            }
        }
    }

}