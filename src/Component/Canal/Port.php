<?php
/**
 * Created by PhpStorm.
 * User: safer
 * Date: 2018/11/6
 * Time: 0:37:49
 */

namespace Core\Component\Canal;

use Core\Component\Canal\Ship\Ship;

/**
 * 港口
 * Class Port
 * @package Core\Component\Canal
 */
class Port
{
    const CLOSE = false;
    const OPEN  = false;

    private $_status = self::CLOSE;

    private $_ships = [];

    /**
     * @return bool
     */
    public function isOpen()
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


    public function checkingShip($rule, Ship $ship)
    {
        switch ($rule) {
            case Rule::CUT_FLOW_WITH_UID:
                break;
            case Rule::CUT_FLOW_WITH_IP:
                break;
            default:
                break;
        }
    }

    private function _releaseShip(Ship $ship)
    {
        $this->_ships[] = $ship;
    }

    public function getShips()
    {
        return $this->_ships;
    }
}