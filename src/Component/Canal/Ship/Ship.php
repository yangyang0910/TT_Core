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
    const LEGALITY = true; // 合法
    const ILLEGAL  = false; // 非法

    private $_legality = self::ILLEGAL; // 合法性

    /**
     * @var Course
     */
    private $_course;

    public function __construct(Course $course)
    {
        $this->_course = $course;
    }

    /**
     * 判断合法性
     * @return bool
     */
    public function isLegality()
    {
        return $this->_legality;
    }

    /**
     * 设置合法性
     * @param $legality
     * @return $this
     */
    public function setLegality($legality)
    {
        $this->_legality = $legality;
        return $this;
    }

    /**
     * 获取航向
     * @return Course
     */
    public function getCourse()
    {
        return $this->_course;
    }

    /**
     * 设置航向
     * @param $course
     * @return $this
     */
    public function setCourse(Course $course)
    {
        $this->_course = $course;
        return $this;
    }
}