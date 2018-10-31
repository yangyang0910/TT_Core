<?php
/**
 * Created by PhpStorm.
 * User: safer
 * Date: 2018/10/28
 * Time: 1:35:48
 */

namespace Core\Component;


class MultiContainer
{
    private $container = [];
    private $allowKeys = null;

    function __construct(array $allowKeys = null)
    {
        $this->allowKeys = $allowKeys;
    }

    function add($key, $item)
    {
        if (is_array($this->allowKeys) && !in_array($key, $this->allowKeys)) {
            return false;
        }
        $this->container[$key][] = $item;
        return $this;
    }

    function set($key, $item)
    {
        if (is_array($this->allowKeys) && !in_array($key, $this->allowKeys)) {
            return false;
        }
        $this->container[$key] = [$item];
        return $this;
    }

    function delete($key)
    {
        if (isset($this->container[$key])) {
            unset($this->container[$key]);
        }
        return $this;
    }

    function get($key)
    {
        if (isset($this->container[$key])) {
            return $this->container[$key];
        } else {
            return null;
        }
    }

    function all()
    {
        return $this->container;
    }
}