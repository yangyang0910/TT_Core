<?php
/**
 * Created by PhpStorm.
 * User: safer
 * Date: 2018/10/28
 * Time: 1:37:04
 */

namespace Core\Component;

use \Core\Swoole\Server;
use \Swoole\Process;
use \Swoole\Coroutine;

class Invoker
{
    /*
     *  Async::set([
          'enable_signalfd' => false,
       ]);
     */
    /**
     * @param callable  $callable
     * @param float|int $timeOut
     * @param mixed     ...$params
     * @return mixed|null
     * @throws \Throwable
     */
    public static function exec(callable $callable, $timeOut = 100 * 1000, ...$params)
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGALRM, function () {
            Process::alarm(-1);
            throw new \RuntimeException('func timeout');
        });
        try {
            Process::alarm($timeOut);
            $ret = self::callUserFunc($callable, ...$params);
            Process::alarm(-1);
            return $ret;
        } catch (\Throwable $throwable) {
            throw $throwable;
        }
    }

    /**
     * @param callable $callable
     * @param mixed    ...$params
     * @return mixed|null
     */
    public static function callUserFunc(callable $callable, ...$params)
    {
        if (SWOOLE_VERSION > 1) {
            if ($callable instanceof \Closure) {
                return $callable(...$params);
            } elseif (is_array($callable) && is_object($callable[0])) {
                $class  = $callable[0];
                $method = $callable[1];
                return $class->$method(...$params);
            } elseif (is_array($callable) && is_string($callable[0])) {
                $class  = $callable[0];
                $method = $callable[1];
                return $class::$method(...$params);
            } elseif (is_string($callable)) {
                return $callable(...$params);
            } else {
                return null;
            }
        } else {
            return call_user_func($callable, ...$params);
        }
    }

    /**
     * @param callable $callable
     * @param array    $params
     * @return mixed|null
     */
    public static function callUserFuncArray(callable $callable, array $params)
    {
        if (SWOOLE_VERSION > 1) {
            if ($callable instanceof \Closure) {
                return $callable(...$params);
            } elseif (is_array($callable) && is_object($callable[0])) {
                $class  = $callable[0];
                $method = $callable[1];
                return $class->$method(...$params);
            } elseif (is_array($callable) && is_string($callable[0])) {
                $class  = $callable[0];
                $method = $callable[1];
                return $class::$method(...$params);
            } elseif (is_string($callable)) {
                return $callable(...$params);
            } else {
                return null;
            }
        } else {
            return call_user_func_array($callable, $params);
        }
    }
}