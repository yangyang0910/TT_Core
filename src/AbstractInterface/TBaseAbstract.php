<?php
/**
 * Created by PhpStorm.
 * User: yangcai
 * Date: 2018/8/21
 * Time: 18:31
 */

namespace Core\AbstractInterface;

use Core\Component\Di;

/**
 * Trait TBaseAbstract
 * @package Core\AbstractInterface
 */
trait TBaseAbstract
{
    /**
     * @var Di
     */
    static protected $di;

    /**
     * AMiddleware constructor.
     *
     * @param mixed ...$args
     */
    function __construct(...$args)
    {
        self::$di = Di::getInstance();
        $this->initialize($args);
    }

    /**
     * @param mixed ...$args
     */
    abstract function initialize(...$args);

    /**
     * @return Di
     */
    static final protected function di()
    {
        return self::$di;
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return $this|null
     */
    public function __call($name, $arguments)
    {
        if (strstr($name, 'get')) {
            $property = lcfirst(str_replace('get', '', $name));
            return $this->$property;
        } elseif (strstr($name, 'set')) {
            $property        = lcfirst(str_replace('set', '', $name));
            $this->$property = current($arguments);
            return $this;
        }
        return NULL;
    }
}