<?php
/**
 * Created by PhpStorm.
 * User: safer
 * Date: 2018/11/6
 * Time: 0:52:09
 */

namespace Core\Component\Canal\Ship;

/**
 * 航向
 * Class Course
 * @package Core\Component\Canal
 */
class Course
{
    const TO_OLD_RIVER    = 'TO_OLD_RIVER'; // 流向新河流
    const TO_NEW_RIVER    = 'TO_NEW_RIVER'; // 流向旧河流
    const TO_DOUBLE_RIVER = 'TO_DOUBLE_RIVER'; // 流向双河流

    private $_course;

    /**
     * @return mixed
     */
    public function getCourse()
    {
        return $this->_course;
    }

    /**
     * @param $course
     * @return $this
     */
    public function setCourse($course)
    {
        $this->_course = $course;
        return $this;
    }
}