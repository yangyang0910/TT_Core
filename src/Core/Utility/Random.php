<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 16/9/5
 * Time: 下午8:40
 */

namespace Core\Utility;


/**
 * Class Random
 * @package Core\Utility
 */
class Random
{
    /**
     * @param $length
     * @return bool|string
     */
    static function randStr($length)
    {
        return substr(str_shuffle("abcdefghijkmnpqrstuvwxyzABCDEFGHIJKMNPQRSTUVWXYZ23456789"), 0, $length);
    }

    /**
     * @param $length
     * @return string
     */
    static function randNumStr($length)
    {
        $chars    = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $password = '';
        while (strlen($password) < $length) {
            $password .= $chars[rand(0, 9)];
        }
        return $password;
    }

    /**
     * 生成随机字符串 可用于生成随机密码等
     * @param int    $length   生成长度
     * @param string $alphabet 自定义生成字符集
     * @author : evalor <master@evalor.cn>
     * @return bool|string
     */
    static function character($length = 6, $alphabet = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789')
    {
        /*
         * mt_srand() is to fix:
            mt_rand(0,100);
            if(pcntl_fork()){
                var_dump(mt_rand(0,100));
            }else{
                var_dump(mt_rand(0,100));
            }
         */
        mt_srand();
        // 重复字母表以防止生成长度溢出字母表长度
        if ($length >= strlen($alphabet)) {
            $rate = intval($length / strlen($alphabet)) + 1;
            $alphabet = str_repeat($alphabet, $rate);
        }

        // 打乱顺序返回
        return substr(str_shuffle($alphabet), 0, $length);
    }
}