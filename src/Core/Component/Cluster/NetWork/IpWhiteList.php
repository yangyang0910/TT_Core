<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/8
 * Time: 上午3:28
 */

namespace Core\Component\Cluster\NetWork;


class IpWhiteList
{
    private $list = [];

    function add($ip)
    {
        $this->list[ip2long($ip)] = true;
    }

    function getList()
    {
        return $this->list;
    }

    function filter($ip)
    {
        if (empty($this->list)) {
            return true;
        } else {
            if (isset($this->list[ip2long($ip)])) {
                return true;
            } else {
                return false;
            }
        }
    }
}