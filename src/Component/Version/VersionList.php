<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/11/22
 * Time: 下午10:08
 */

namespace Core\Component\Version;


/**
 * Class VersionList
 * @package Core\Component\Version
 */
class VersionList
{
    /**
     * @var array
     */
    private $list = [];

    /**
     * @param          $name
     * @param callable $judge
     *
     * @return Version
     */
    function add($name, callable $judge)
    {
        $version           = new Version($name, $judge);
        $this->list[$name] = $version;
        return $version;
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    function get($name)
    {
        if (isset($this->list[$name])) {
            return $this->list[$name];
        }
        return null;
    }

    /**
     * @return array
     */
    function all()
    {
        return $this->list;
    }
}