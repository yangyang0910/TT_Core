<?php
/**
 * Created by PhpStorm.
 * User: safer
 * Date: 2018/10/28
 * Time: 1:35:21
 */

namespace Core\Component;

use Core\Component\Error\Trigger;

class Event extends MultiContainer
{
    function add($key, $item)
    {
        if (is_callable($item)) {
            return parent::add($key, $item);
        } else {
            return false;
        }
    }

    function set($key, $item)
    {
        if (is_callable($item)) {
            return parent::set($key, $item);
        } else {
            return false;
        }
    }

    public function hook($event, ...$args)
    {
        $calls = $this->get($event);
        if (is_array($calls)) {
            foreach ($calls as $call) {
                try {
                    Invoker::callUserFunc($call, ...$args);
                } catch (\Throwable $throwable) {
                    Trigger::exception($throwable);
                }
            }
        }
    }
}