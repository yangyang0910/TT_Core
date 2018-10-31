<?php

namespace Core\AbstractInterface;

/**
 * 日志接口
 * Interface ILoggerWriter
 * @package Core\AbstractInterface
 */
interface ILoggerWriter
{
    /**
     * @param $obj
     * @param $logCategory
     * @param $timeStamp
     * @return mixed
     */
    static function writeLog($obj, $logCategory, $timeStamp);
}