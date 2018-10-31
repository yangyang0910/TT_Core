<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/10
 * Time: 下午3:51
 */

namespace Core\Component\IO;


use Core\Component\Spl\SplStream;

/**
 * Class FileIO
 * @package Core\Component\IO
 */
class FileIO extends SplStream
{
    /**
     * FileIO constructor.
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