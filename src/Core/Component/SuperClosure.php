<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/2/8
 * Time: 14:44
 */

namespace Core\Component;

use SuperClosure\Serializer;

/**
 * Class SuperClosure
 * @package Core\Component
 */
class SuperClosure
{
    /**
     * @var \Closure
     */
    protected $func;
    /**
     * @var Serializer
     */
    protected $serializer;
    /**
     * @var
     */
    protected $serializedJson;
    /**
     * @var int
     */
    protected $isSerialized = 0;

    /**
     * SuperClosure constructor.
     * @param \Closure $func
     */
    function __construct(\Closure $func)
    {
        $this->func       = $func;
        $this->serializer = new Serializer();
    }

    /**
     * @return array
     */
    function __sleep()
    {
        $this->serializedJson = $this->serializer->serialize($this->func);
        $this->isSerialized   = 1;
        return ["serializedJson", 'isSerialized'];
    }

    /**
     *
     */
    function __wakeup()
    {
        $this->serializer = new Serializer();
        $this->func       = $this->serializer->unserialize($this->serializedJson);
    }

    /**
     * @return mixed
     */
    function __invoke()
    {
        /*
         * prevent call before serialized
         */
        $args = func_get_args();
        if ($this->isSerialized) {
            $func = $this->serializer->unserialize($this->serializedJson);
        } else {
            $func = $this->func;
        }
        return call_user_func_array($func, $args);
    }
}