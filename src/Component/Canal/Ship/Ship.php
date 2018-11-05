<?php
/**
 * Created by PhpStorm.
 * User: safer
 * Date: 2018/11/6
 * Time: 0:38:54
 */

namespace Core\Component\Canal\Ship;

/**
 * 船只
 * Class Ship
 * @package Core\Component\Canal
 */
class Ship
{
    const LEGALITY = true;
    const ILLEGAL  = false;

    private $_status = self::ILLEGAL;

    /**
     * @return bool
     */
    public function isLegal()
    {
        return $this->_status;
    }

    /**
     * @param bool $status
     */
    public function setStatus($status)
    {
        $this->_status = $status;
    }

}