<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/11/22
 * Time: 下午9:58
 */

namespace Core\Component\Version;


/**
 * Class ARegister
 * @package Core\Component\Version
 */
abstract class ARegister
{
    /**
     * @param VersionList $versionList
     *
     * @return mixed
     */
    abstract function register(VersionList $versionList);
}