<?php

namespace Core\AbstractInterface;

/**
 * 错误处理接口
 * Interface IExceptionHandler
 * @package Core\AbstractInterface
 */
interface IExceptionHandler
{
    /**
     * @param \Exception $exception
     * @return mixed
     */
    function handler(\Exception $exception);

    /**
     * @param \Exception $exception
     * @return mixed
     */
    function display(\Exception $exception);

    /**
     * @param \Exception $exception
     * @return mixed
     */
    function log(\Exception $exception);
}