<?php

namespace Core\AbstractInterface;


use Core\Http\Request;
use Core\Http\Response;

/**
 * swoole 事件基类
 * Class AEvent
 * @package Core\AbstractInterface
 */
abstract class AEvent
{
    /**
     * @var
     */
    protected static $instance;

    /**
     * @param array ...$args
     *
     * @return static
     */
    static function getInstance(...$args)
    {
        if (!isset(self::$instance)) {
            self::$instance = new static($args);
        }
        return self::$instance;
    }

    /**
     * AEvent constructor.
     *
     * @param array ...$args
     */
    final function __construct(...$args)
    {
        if (\method_exists($this, 'initialize')) {
            $this->initialize($args);
        }
    }

    /**
     * @return mixed
     */
    abstract function frameInitialize();

    /**
     * @return mixed
     */
    abstract function frameInitialized();

    /**
     * 未执行swoole_http_server start
     *
     * @param \swoole_server $server
     *
     * @return mixed
     */
    abstract function beforeWorkerStart(\swoole_server $server);

    /*
     * Server启动在主进程的主线程回调此函数
     * 在此事件之前Swoole Server已进行了如下操作
            已创建了manager进程
            已创建了worker子进程
            已监听所有TCP/UDP端口
            已监听了定时器
       接下来要执行
            主Reactor开始接收事件，客户端可以connect到Server
       onStart回调中，仅允许echo、打印Log、修改进程名称。不得执行其他操作。
       onWorkerStart和onStart回调是在不同进程中并行执行的，不存在先后顺序。
       在onStart中创建的全局资源对象不能在worker进程中被使用，因为发生onStart调用时，
       worker进程已经创建好了。新创建的对象在主进程内，worker进程无法访问到此内存区域。
       因此全局对象创建的代码需要放置在swoole_server_start之前。
     */
    /**
     * @param \swoole_server $server
     *
     * @return mixed
     */
    abstract function onStart(\swoole_server $server);

    /*
     * 在此之前Swoole Server已进行了如下操作
            已关闭所有线程
            已关闭所有worker进程
            已close所有TCP/UDP监听端口
            已关闭主Rector
       强制kill进程不会回调onShutdown，如kill -9
       需要使用kill -15来发送SIGTREM信号到主进程才能按照正常的流程终止
     */
    /**
     * @param \swoole_server $server
     *
     * @return mixed
     */
    abstract function onShutdown(\swoole_server $server);

    /*
     * 此事件在worker进程/task进程启动时发生。这里创建的对象可以在进程生命周期内使用
     * 发生PHP致命错误或者代码中主动调用exit时，Worker/Task进程会退出，管理进程会重新创建新的进程
        onWorkerStart/onStart是并发执行的，没有先后顺序
        可以将公用的，不易变的php文件放置到onWorkerStart之前。这样虽然不能重载入代码，
        但所有worker是共享的，不需要额外的内存来保存这些数据。
        onWorkerStart之后的代码每个worker都需要在内存中保存一份
        $worker_id是一个从0-$worker_num之间的数字，表示这个worker进程的ID
        $worker_id和进程PID没有任何关系
     * 每个worker进程启动均会执行该函数，6个worker就执行6次
     */
    /**
     * @param \swoole_server $server
     * @param                $workerId
     *
     * @return mixed
     */
    abstract function onWorkerStart(\swoole_server $server, $workerId);

    /*
    * 每个worker进程启动均会执行该函数，6个worker就执行6次
    */
    /**
     * @param \swoole_server $server
     * @param                $workerId
     *
     * @return mixed
     */
    abstract function onWorkerStop(\swoole_server $server, $workerId);

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return mixed
     */
    abstract function onRequest(Request $request, Response $response);

    /**
     * @param Request  $request
     * @param Response $response
     * @param          $targetControllerClass
     * @param          $targetAction
     *
     * @return bool
     */
    abstract function onDispatcher(Request $request, Response $response, $targetControllerClass, $targetAction);

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return mixed
     */
    abstract function onResponse(Request $request, Response $response);

    /**
     * @param \swoole_server $server
     * @param                $taskId
     * @param                $workerId
     * @param                $callBackObj
     *
     * @return mixed
     */
    abstract function onTask(\swoole_server $server, $taskId, $workerId, $callBackObj);

    /**
     * @param \swoole_server $server
     * @param                $taskId
     * @param                $callBackObj
     *
     * @return mixed
     */
    abstract function onFinish(\swoole_server $server, $taskId, $callBackObj);

    /**
     * @param \swoole_server $server
     * @param                $worker_id
     * @param                $worker_pid
     * @param                $exit_code
     *
     * @return mixed
     */
    abstract function onWorkerError(\swoole_server $server, $worker_id, $worker_pid, $exit_code);

    /**
     * @param \swoole_server $server
     * @param                $frame
     *
     * @return mixed
     */
    abstract function onMessage(\swoole_server $server, $frame);
}