<?php
/**
 * Created by PhpStorm.
 * User: yangcai
 * Date: 2018/5/16
 * Time: 19:02
 */

namespace Core\AbstractInterface;

use think\Model;
use Core\Component\Di;

/**
 * model 基类
 * Class AModel
 * @package Core\AbstractInterface
 */
abstract class AModel extends Model
{
    /**
     * @var Di
     */
    static protected $di;
    /**
     * @var bool
     */
    protected $autoWriteTimestamp = false;
    /**
     * @var string
     */
    protected $createTime = 'create_at';
    /**
     * @var string
     */
    protected $updateTime = 'update_at';

    protected function initialize()
    {
        self::$di = Di::getInstance();
    }

    /**
     * @return Di
     */
    static final protected function di()
    {
        return self::$di;
    }
}