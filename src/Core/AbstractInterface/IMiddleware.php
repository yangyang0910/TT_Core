<?php
/**
 * Created by PhpStorm.
 * User: yangcai
 * Date: 2018/8/21
 * Time: 17:49
 */

namespace Core\AbstractInterface;

/**
 * 中间件
 * Interface IMiddleware
 * @package Core\AbstractInterface
 */
interface IMiddleware
{
    public function handle();
}