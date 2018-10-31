<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/2/1
 * Time: 上午12:23
 */

namespace Core\Component;

use Core\Component\Error\Trigger;

/**
 * Class Di
 * @package Core\Component
 */
class Di
{
    /**
     * @var
     */
    protected static $instance;
    /**
     * @var array
     */
    protected $container = [];

    /**
     * @return static
     */
    static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * @param       $key
     * @param       $obj
     * @param array ...$arg
     * @return $this
     */
    function set($key, $obj, ...$arg)
    {
        if (count($arg) == 1 && is_array($arg[0])) {
            $arg = $arg[0];
        }
        $this->container[$key] = [
            "obj"    => $obj,
            "params" => $arg,
        ];
        return $this;
    }

    /**
     * @param $key
     */
    function delete($key)
    {
        unset($this->container[$key]);
    }

    /**
     *
     */
    function clear()
    {
        $this->container = [];
    }

    /**
     * @param $key
     * @return null|string
     */
    function get($key)
    {
        if (isset($this->container[$key])) {
            $result = $this->container[$key];
            if (is_callable($result['obj'])) {
                return call_user_func_array($result['obj'], $result['params']);
//                $ret                          = call_user_func_array($result['obj'], $result['params']);
//                $this->container[$key]['obj'] = $ret;
//                return $this->container[$key]['obj'];
            } elseif (is_object($result['obj']) and (!$result['obj'] instanceof \Closure)) {
                return $result['obj'];
            } elseif (is_string($result['obj']) and class_exists($result['obj'])) {
                try {
                    $reflection = new \ReflectionClass ($result['obj']);
                    if ($key == '') {
                        $ins = $reflection->newInstanceArgs($result['params']);
                    }
                    $this->container[$key]['obj'] = $ins;
                    return $this->container[$key]['obj'];
                } catch (\Exception $e) {
                    Trigger::exception($e);
                }
            } else {
                return $result['obj'];
            }
        } else {
            return null;
        }
    }
}