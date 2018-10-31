<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/8/29
 * Time: 下午12:35
 */

namespace Core\Utility;


/**
 * Class Judge
 * @package Core\Utility
 */
class Judge
{
    /*
     * 说明  了防止新人出现
     *
     * if(empty(0)){}
     *
     * if(md5("400035577431") == md5("mcfog_42r6i8"))
     *
     * 的问题
     */
    /**
     * @param $val
     * @param $val2
     * @return bool
     */
    static function isEqual($val, $val2)
    {
        if ($val == $val2) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @param $val
     * @param $val2
     * @return bool
     */
    static function isStrictEqual($val, $val2)
    {
        if ($val === $val2) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @param $val
     * @return bool
     */
    static function isNull($val)
    {
        return is_null($val);
    }

    /**
     * 注意  0不为空，为解决  php内0为空问题
     * @param $val
     * @return bool
     */
    static function isEmpty($val)
    {
        if ($val === 0 || $val === '0') {
            return FALSE;
        } else {
            return empty($val);
        }
    }

    /**
     * 接受  0，1 true，false
     * @param      $val
     * @param bool $strict
     * @return bool
     */
    static function boolean($val, $strict = FALSE)
    {
        if ($strict) {
            return is_bool($val);
        } else {
            if (is_bool($val) || $val == 0 || $val == 1) {
                return TRUE;
            } else {
                return FALSE;
            }

        }
    }

    /**
     * @param      $val
     * @param bool $strict
     * @return bool
     */
    static function isTrue($val, $strict = FALSE)
    {
        if ($strict) {
            if ($val === TRUE) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            if ($val == 1) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    /**
     * @param      $val
     * @param bool $strict
     * @return bool
     */
    static function isFalse($val, $strict = FALSE)
    {
        if ($strict) {
            if ($val === FALSE) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            if ($val == 0) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }
}