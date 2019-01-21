<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/26
 * Time: 上午12:54
 */

namespace Core\Component\Pool;


use Core\AbstractInterface\TSingleton;

class PoolManager
{
    use TSingleton;

    private $pool = [];
    private $defaultConfig;

    function __construct()
    {
        $this->defaultConfig = new PoolConf();
    }

    function getDefaultConfig()
    {
        return $this->defaultConfig;
    }

    /**
     * @param $className
     * @param int $maxNum
     * @return PoolConf|null
     * @throws \ReflectionException
     */
    function register($className, $maxNum = 20)
    {
        $ref = new \ReflectionClass($className);
        if ($ref->isSubclassOf(AbstractPool::class)) {
            $conf = clone $this->defaultConfig;
            $conf->setClass($className);
            $conf->setMaxObjectNum($maxNum);
            $this->pool[$this->generateKey($className)] = $conf;
            return $conf;
        } else {
            return null;
        }
    }

    /**
     * 请在进程克隆后，也就是worker start后，每个进程中独立使用
     * @param $className
     * @return mixed|null
     * @throws \ReflectionException
     */
    function getPool($className)
    {
        $key = $this->generateKey($className);
        if (isset($this->pool[$key])) {
            $item = $this->pool[$key];
            if ($item instanceof AbstractPool) {
                return $item;
            } else if ($item instanceof PoolConf) {
                $className = $item->getClass();
                $obj = new $className($item);
                $this->pool[$key] = $obj;
                return $obj;
            }
        } else if (class_exists($className)) {
            if (!$this->register($className)) {
                $config = clone $this->defaultConfig;
                $config->setClass($className);
                $pool = new class($config) extends AbstractPool
                {
                    protected function createObject()
                    {
                        // TODO: Implement createObject() method.
                        $className = $this->getPoolConfig()->getClass();
                        return new $className;
                    }
                };
                $this->pool[$key] = $pool;
            }
            return $this->getPool($className);
        }
        return null;
    }

    private function generateKey($class)
    {
        return substr(md5($class), 8, 16);
    }
}