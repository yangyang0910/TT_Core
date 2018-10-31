<?php

namespace Core\AbstractInterface;

/**
 * 错误处理接口
 * Interface IErrorHandler
 * @package Core\AbstractInterface
 */
interface IErrorHandler
{
    /**
     * @param      $msg
     * @param null $file
     * @param null $line
     * @param null $errorCode
     * @param      $trace
     */
    function handler($msg, $file = null, $line = null, $errorCode = null, $trace);

    /**
     * @param      $msg
     * @param null $file
     * @param null $line
     * @param null $errorCode
     * @param      $trace
     */
    function display($msg, $file = null, $line = null, $errorCode = null, $trace);

    /**
     * @param      $msg
     * @param null $file
     * @param null $line
     * @param null $errorCode
     * @param      $trace
     */
    function log($msg, $file = null, $line = null, $errorCode = null, $trace);
}