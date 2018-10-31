<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/5
 * Time: 上午11:56
 */

namespace Core\Utility\Validate;


/**
 * Class Error
 * @package Core\Utility\Validate
 */
class Error
{
    /**
     * @var array
     */
    private $error;

    /**
     * Error constructor.
     * @param array $error
     */
    function __construct(array $error)
    {
        $this->error = $error;
    }

    /**
     * @return mixed
     */
    function first()
    {
        return array_shift($this->error);
    }

    /**
     * @return array
     */
    function all()
    {
        return $this->error;
    }
}