<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/3
 * Time: 下午9:37
 */

namespace Core\Utility\Validate;


/**
 * Class Field
 * @package Core\Utility\Validate
 */
class Field
{
    /**
     * @var null
     */
    protected $currentRule = null;
    /**
     * @var array
     */
    protected $rule = [];
    /**
     * @var array
     */
    protected $msg = [
        '__default__' => null,
    ];

    /**
     * @param $msg
     * @return $this
     */
    function withMsg($msg)
    {
        if (isset($this->currentRule)) {
            $this->msg[$this->currentRule] = $msg;
            $this->currentRule             = null;
        } else {
            $this->msg['__default__'] = $msg;
        }
        return $this;
    }

    /**
     * @param       $rule
     * @param array ...$arg
     * @return $this
     */
    function withRule($rule, ...$arg)
    {
        $this->currentRule = $rule;
        $this->rule[$rule] = $arg;
        return $this;
    }

    /**
     * @return array
     */
    function getRule()
    {
        return $this->rule;
    }

    /**
     * @return array
     */
    function getMsg()
    {
        return $this->msg;
    }

}