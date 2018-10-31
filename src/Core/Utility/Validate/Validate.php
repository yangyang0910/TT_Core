<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/3
 * Time: 下午9:25
 */

namespace Core\Utility\Validate;


use Core\Component\Spl\SplArray;

/**
 * Class Validate
 * @package Core\Utility\Validate
 */
class Validate
{
    /**
     * @var array
     */
    protected $map = [];

    /**
     * @param $field
     * @return Field|mixed
     */
    function addField($field)
    {
        if (isset($this->map[$field])) {
            $instance = $this->map[$field];
        } else {
            $instance          = new Field();
            $this->map[$field] = $instance;
        }
        return $instance;
    }

    /**
     * @param array $data
     * @return Message
     */
    function validate(array $data)
    {
        $error = [];
        $data  = new SplArray($data);
        foreach ($this->map as $filed => $fieldInstance) {
            $rules = $fieldInstance->getRule();
            $msg   = $fieldInstance->getMsg();
            if (isset($rules[Rule::OPTIONAL]) && empty($data->get($filed))) {
                continue;
            } else {
                foreach ($rules as $rule => $args) {
                    if (!Func::$rule($filed, $data, $args)) {
                        $error[$filed][$rule] = isset($msg[$rule]) ? $msg[$rule] : $msg['__default__'];
                    }
                }
            }
        }
        return new Message($error);
    }
}