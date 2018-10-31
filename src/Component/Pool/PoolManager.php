<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/3
 * Time: 下午1:21
 */

namespace Core\Component\Pool;


use Core\Conf\Config;
use Core\Component\Pool\AbstractInterface\Pool;
use Core\Component\Error\Trigger;
use Core\Swoole\Memory\TableManager;
use Swoole\Table;

/**
 * Class PoolManager
 * @package Core\Component\Pool
 */
class PoolManager
{
    /**
     * @var null|\swoole_table
     */
    private $poolTable = NULL;
    /**
     * @var array
     */
    private $poolClassList = [];
    /**
     * @var array
     */
    private $poolObjectList = [];

    /**
     *
     */
    const TYPE_ONLY_WORKER = 1;
    /**
     *
     */
    const TYPE_ONLY_TASK_WORKER = 2;
    /**
     *
     */
    const TYPE_ALL_WORKER = 3;

    /**
     * @var
     */
    protected static $instance;

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
     * PoolManager constructor.
     */
    function __construct()
    {
        TableManager::getInstance()->add('__PoolManager', [
            'createNum' => ['type' => Table::TYPE_INT, 'size' => 3]
        ], 8192);
        $this->poolTable = TableManager::getInstance()->get('__PoolManager');

        $conf = Config::getInstance()->getConf('POOL_MANAGER');
        if (is_array($conf)) {
            foreach ($conf as $class => $item) {
                $this->registerPool($class, $item['min'], $item['max'], $item['type']);
            }
        }
    }

    /**
     * @param string $class
     * @param        $minNum
     * @param        $maxNum
     * @param int    $type
     * @return bool
     */
    function registerPool($class, $minNum, $maxNum, $type = self::TYPE_ONLY_WORKER)
    {
        try {
            $ref = new \ReflectionClass($class);
            if ($ref->isSubclassOf(Pool::class)) {
                $this->poolClassList[$class] = [
                    'min'  => $minNum,
                    'max'  => $maxNum,
                    'type' => $type
                ];
                return TRUE;
            } else {
                Trigger::error($class . ' is not Pool class');
            }
        } catch (\Throwable $throwable) {
            Trigger::error($throwable);
        }
        return FALSE;
    }

    /**
     * @param string $class
     * @return Pool|null
     */
    function getPool($class)
    {
        if (isset($this->poolObjectList[$class])) {
            return $this->poolObjectList[$class];
        } else {
            return NULL;
        }
    }

    /**
     * 为自定义进程预留
     * @param $workerId
     */
    function __workerStartHook($workerId)
    {
        $workerNum = Config::getInstance()->getConf('MAIN_SERVER.SETTING.worker_num');
        foreach ($this->poolClassList as $class => $item) {
            if ($item['type'] === self::TYPE_ONLY_WORKER) {
                if ($workerId > ($workerNum - 1)) {
                    continue;
                }
            } elseif ($item['type'] === self::TYPE_ONLY_TASK_WORKER) {
                if ($workerId <= ($workerNum - 1)) {
                    continue;
                }
            }
            $key = self::generateTableKey($class, $workerId);
            $this->poolTable->del($key);
            $this->poolObjectList[$class] = new $class($item['min'], $item['max'], $key);
        }
    }

    /**
     * @return null|\swoole_table
     */
    function getPoolTable()
    {
        return $this->poolTable;
    }

    /**
     * @param string $class
     * @param int    $workerId
     * @return string
     */
    public static function generateTableKey($class, $workerId)
    {
        return substr(md5($class . $workerId), 8, 16);
    }

}