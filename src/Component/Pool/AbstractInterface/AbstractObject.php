<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/3
 * Time: 下午2:16
 */

namespace Core\Component\Pool\AbstractInterface;


/**
 * Class AbstractObject
 * @package Core\Component\Pool\AbstractInterface
 */
abstract class AbstractObject
{
    /**
     * @return mixed
     */
    protected abstract function gc();

    /**
     * @return mixed
     */
    abstract function initialize();

    /**
     *
     */
    function __destruct()
    {
        $this->gc();
    }
}