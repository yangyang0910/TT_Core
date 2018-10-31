<?php
/**
 * Created by PhpStorm.
 * User: safer
 * Date: 2018/8/3
 * Time: 0:06:36
 */

namespace Core\Utility\Logic;

use Core\AbstractInterface\TSingleton;

/**
 * Class ACodePhrase
 * @package Core\Utility\Logic
 */
class ACodePhrase
{

//    use Singleton;

    /**
     * @var array
     */
    protected static $phrases = [];

    /**
     * @param $statusCode
     * @return mixed|null
     */
    static function getReasonPhrase($statusCode)
    {
        if (isset(static::$phrases[$statusCode])) {
            return static::$phrases[$statusCode];
        } else {
            return null;
        }
    }
}