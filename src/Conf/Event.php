<?php

namespace Core\Conf;

use Core\AbstractInterface\AEvent;
use Core\Http\Request;
use Core\Http\Response;
use Core\AutoLoader;

/**
 * Class Event
 * @package Core\Conf
 */
class Event extends AEvent
{
    /**
     * @var AEvent
     */
    private $extendedEvent;

    function initialize()
    {
        $className = APP_NAME . '\Conf\SwooleEvent';
        if (class_exists($className)) {
            $this->extendedEvent = new $className;
        }else{
            $this->extendedEvent = null;
        }
    }

    /**
     * @return mixed|void
     */
    function frameInitialize()
    {
        AutoLoader::getInstance()->requireFile('vendor/autoload.php');
        if ($this->extendedEvent) {
            $this->extendedEvent->frameInitialize();
        }
    }

    /**
     * @return mixed|void
     */
    function frameInitialized()
    {
        if ($this->extendedEvent) {
            $this->extendedEvent->frameInitialized();
        }
    }

    /**
     * @param \swoole_server $server
     *
     * @return mixed|void
     */
    function beforeWorkerStart(\swoole_server $server)
    {
        if ($this->extendedEvent) {
            $this->extendedEvent->beforeWorkerStart($server);
        }
    }

    /**
     * @param \swoole_server $server
     *
     * @return mixed|void
     */
    function onStart(\swoole_server $server)
    {
        if ($this->extendedEvent) {
            $this->extendedEvent->onStart($server);
        }
    }

    /**
     * @param \swoole_server $server
     *
     * @return mixed|void
     */
    function onShutdown(\swoole_server $server)
    {
        if ($this->extendedEvent) {
            $this->extendedEvent->onShutdown($server);
        }
    }

    /**
     * @param \swoole_server $server
     * @param                $workerId
     *
     * @return mixed|void
     */
    function onWorkerStart(\swoole_server $server, $workerId)
    {
        $this->_AutoReload($server, $workerId);
        if ($this->extendedEvent) {
            $this->extendedEvent->onWorkerStart($server, $workerId);
        }
    }

    /**
     * @param \swoole_server $server
     * @param                $workerId
     *
     * @return mixed|void
     */
    function onWorkerStop(\swoole_server $server, $workerId)
    {
        if ($this->extendedEvent) {
            $this->extendedEvent->onWorkerStop($server, $workerId);
        }
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return mixed|void
     */
    function onRequest(Request $request, Response $response)
    {
        if ($this->extendedEvent) {
            $this->extendedEvent->onRequest($request, $response);
        }
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param          $targetControllerClass
     * @param          $targetAction
     *
     * @return bool
     */
    function onDispatcher(Request $request, Response $response, $targetControllerClass, $targetAction)
    {
        if ($this->extendedEvent) {
            return $this->extendedEvent->onDispatcher($request, $response, $targetControllerClass, $targetAction);
        }
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return mixed|void
     */
    function onResponse(Request $request, Response $response)
    {
        if ($this->extendedEvent) {
            $this->extendedEvent->onResponse($request, $response);
        }
    }

    /**
     * @param \swoole_server $server
     * @param                $taskId
     * @param                $workerId
     * @param                $taskObj
     *
     * @return mixed|void
     */
    function onTask(\swoole_server $server, $taskId, $workerId, $taskObj)
    {
        if ($this->extendedEvent) {
            $this->extendedEvent->onTask($server, $taskId, $workerId, $taskObj);
        }
    }

    /**
     * @param \swoole_server $server
     * @param                $taskId
     * @param                $taskObj
     *
     * @return mixed|void
     */
    function onFinish(\swoole_server $server, $taskId, $taskObj)
    {
        if ($this->extendedEvent) {
            $this->extendedEvent->onFinish($server, $taskId, $taskObj);
        }
    }

    /**
     * @param \swoole_server $server
     * @param                $workerId
     * @param                $workerPid
     * @param                $exitCode
     *
     * @return mixed|void
     */
    function onWorkerError(\swoole_server $server, $workerId, $workerPid, $exitCode)
    {
        if ($this->extendedEvent) {
            $this->extendedEvent->onWorkerError($server, $workerId, $workerPid, $exitCode);
        }
    }

    /**
     * @param \swoole_server $server
     * @param                $frame
     *
     * @return mixed|void
     */
    function onMessage(\swoole_server $server, $frame)
    {
        if ($this->extendedEvent) {
            $this->extendedEvent->onMessage($server, $frame);
        }
    }

    /**
     * @param \swoole_server $server
     * @param                $workerId
     */
    private function _AutoReload(\swoole_server $server, $workerId)
    {
        if ($workerId == 0) {
            if ('dev' === APP_ENV) {
                \Core\Swoole\Timer::loop(3000, function () {
                    \Core\Swoole\Server::getInstance()->getServer()->reload();
                });
            } else {
//                $pid        = file_get_contents(Config::getInstance()->getConf("SERVER.CONFIG.pid_file"));
//                $autoReload = new \Core\Swoole\AutoReload($pid);
                $autoReload = new \Core\Swoole\AutoReload();
                $autoReload->watch(ROOT . "/App");
                $autoReload->run();
            }
        }
    }
}
