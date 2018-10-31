<?php

namespace Core\AbstractInterface;

use Core\Http\Request;
use Core\Http\Response;

/**
 * http 错误处理接口
 * Interface IHttpExceptionHandler
 * @package Core\AbstractInterface
 */
interface IHttpExceptionHandler
{
    /**
     * @param \Exception $exception
     * @param Request    $request
     * @param Response   $response
     * @return mixed
     */
    function handler(\Exception $exception, Request $request, Response $response);
}