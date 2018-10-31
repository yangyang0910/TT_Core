<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/8/1
 * Time: 下午3:15
 */

namespace Core\Component\RPC\Standard\Bean;


class IpWhiteList
{
    protected $list = ['127.0.0.1'];


    public function add($ip)
    {
        array_push($this->list, $ip);
        return $this;
    }

    public function set(array $list)
    {
        $this->list = $list;
        return $this;
    }

    public function getList()
    {
        return $this->list;
    }

    public function check($ip)
    {
        return in_array($ip, $this->list);
    }

}