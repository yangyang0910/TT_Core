<?php
/**
 * Created by PhpStorm.
 * User: safer
 * Date: 2018/11/6
 * Time: 0:41:03
 */

namespace Core\Component\Canal\River;

use Core\Component\Canal\Ship\Course;
use Core\Component\Canal\Ship\Ship;

/**
 * 水流
 * Class WaterFlow
 * @package Core\Component\Canal\River
 */
class WaterFlow
{

    /**
     * @var Ship
     */
    private $_ship;

    /**
     * @throws \Exception
     */
    public function flow()
    {
        if (null === $this->_ship) {
            throw  new \Exception('Ship not found');
        }
        switch ($this->_ship->getCourse()->getCourse()) {
            case Course::TO_NEW_RIVER:
                $this->_flowToNewRiver();
                break;
            case Course::TO_OLD_RIVER:
                $this->_flowToOldRiver();
                break;
            case Course::TO_DOUBLE_RIVER:
                $this->_flowToDoubleRiver();
                break;
            default:
                break;
        }
    }

    /**
     * @return Ship
     */
    public function getShip()
    {
        return $this->_ship;
    }

    /**
     * @param $ship
     * @return $this
     */
    public function setShip(Ship $ship)
    {
        $this->_ship = $ship;
        return $this;
    }

    /**
     * 流向新河流
     */
    private function _flowToNewRiver()
    {

    }

    /**
     * 流向旧河流
     */
    private function _flowToOldRiver()
    {

    }

    /**
     * 流向双河流
     */
    private function _flowToDoubleRiver()
    {

    }
}