<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/3/7
 * Time: 上午11:56
 */

namespace Core\Component\Spl;


/**
 * Class SplFileStream
 * @package Core\Component\Spl
 */
class SplFileStream extends SplStream
{
    /**
     * SplFileStream constructor.
     * @param        $file
     * @param string $mode
     */
    function __construct($file, $mode = 'c+')
    {
        $fp = fopen($file, $mode);
        parent::__construct($fp);
    }

    /**
     * @param int $mode
     * @return bool
     */
    function lock($mode = LOCK_EX)
    {
        return flock($this->getStreamResource(), $mode);
    }

    /**
     * @param int $mode
     * @return bool
     */
    function unlock($mode = LOCK_UN)
    {
        return flock($this->getStreamResource(), $mode);
    }
}