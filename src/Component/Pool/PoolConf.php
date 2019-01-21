<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/4
 * Time: 12:05 PM
 */

namespace Core\Component\Pool;


class PoolConf
{
    protected $class;
    protected $intervalCheckTime = 30 * 1000;
    protected $maxIdleTime = 15;
    protected $maxObjectNum = 20;
    protected $minObjectNum = 5;
    protected $getObjectTimeout = 0.5;

    protected $extraConf = [];

    function __construct($class = null)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    public function setClass($className)
    {
        $this->class = $className;
    }


    /**
     * @return float|int
     */
    public function getIntervalCheckTime()
    {
        return $this->intervalCheckTime;
    }

    /**
     * @param $intervalCheckTime
     * @return PoolConf
     */
    public function setIntervalCheckTime($intervalCheckTime)
    {
        $this->intervalCheckTime = $intervalCheckTime;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxIdleTime()
    {
        return $this->maxIdleTime;
    }

    /**
     * @param int $maxIdleTime
     * @return PoolConf
     */
    public function setMaxIdleTime($maxIdleTime)
    {
        $this->maxIdleTime = $maxIdleTime;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxObjectNum()
    {
        return $this->maxObjectNum;
    }

    /**
     * @param int $maxObjectNum
     * @return PoolConf
     */
    public function setMaxObjectNum($maxObjectNum)
    {
        $this->maxObjectNum = $maxObjectNum;
        return $this;
    }

    /**
     * @return float
     */
    public function getGetObjectTimeout()
    {
        return $this->getObjectTimeout;
    }

    /**
     * @param float $getObjectTimeout
     * @return PoolConf
     */
    public function setGetObjectTimeout($getObjectTimeout)
    {
        $this->getObjectTimeout = $getObjectTimeout;
        return $this;
    }

    /**
     * @return array
     */
    public function getExtraConf()
    {
        return $this->extraConf;
    }

    /**
     * @param array $extraConf
     * @return PoolConf
     */
    public function setExtraConf(array $extraConf)
    {
        $this->extraConf = $extraConf;
        return $this;
    }

    /**
     * @return int
     */
    public function getMinObjectNum()
    {
        return $this->minObjectNum;
    }

    /**
     * @param int $minObjectNum
     * @return PoolConf
     */
    public function setMinObjectNum($minObjectNum)
    {
        $this->minObjectNum = $minObjectNum;
        return $this;
    }

}