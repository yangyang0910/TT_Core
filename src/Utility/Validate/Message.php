<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/5
 * Time: 上午11:39
 */

namespace Core\Utility\Validate;


/**
 * Class Message
 * @package Core\Utility\Validate
 */
class Message
{
    /**
     * @var array
     */
    private $error;

    /**
     * Message constructor.
     * @param array $error
     */
    function __construct(array $error)
    {
        $this->error = $error;
    }

    /**
     * @return bool
     */
    function hasError()
    {
        return !empty($this->error);
    }

    /**
     * @param $filed
     * @return Error
     */
    function getError($filed)
    {
        if (isset($this->error[$filed])) {
            return new Error($this->error[$filed]);
        } else {
            /*
             * 预防调用错误
             */
            return new Error([]);
        }
    }

    /**
     * @return array
     */
    function all()
    {
        return $this->error;
    }

    /**
     * @return Error
     */
    function first()
    {
        if ($this->hasError()) {
            return new Error(array_shift($this->error));
        } else {
            /*
             * 预防调用错误
             */
            return new Error([]);
        }
    }
}