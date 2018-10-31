<?php
/**
 * Created by PhpStorm.
 * User: yangcai
 * Date: 2018/7/10
 * Time: 12:25
 */

namespace Core\AbstractInterface;

/**
 * 单例基类
 * Trait Singleton
 * @package Common\Library\Frame
 */
trait TSingleton
{
    /**
     * @var array
     */
    private static $instance = [];

    /**
     * @param mixed ...$args
     *
     * @return static
     */
    static function getInstance(...$args)
    {
        if (!isset(self::$instance[static::class])) {
            self::$instance[static::class] = new static(...$args);
        }
        return self::$instance[static::class];
    }
}