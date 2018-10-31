<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/14
 * Time: 上午12:37
 */

namespace Core\Component\Cluster\Callback;


use Core\Component\Event;

class ClusterShutdown extends Event
{
    /*
     * 仅在守护模式的时候，会被执行
     */
}