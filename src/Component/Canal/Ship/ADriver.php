<?php
/**
 * Created by PhpStorm.
 * User: safer
 * Date: 2018/11/6
 * Time: 0:58:11
 */

namespace Core\Component\Canal\Ship;

/**
 * 驱动
 * Class ADriver
 * @package Core\Component\Canal\Ship
 */
abstract class ADriver
{

    protected $_startUid;
    protected $_endUid;
    protected $_userIds = [];
    protected $_startIp;
    protected $_endIp;
    protected $_Ips     = [];

    /**
     * @return mixed
     */
    abstract function getStartUid();

    /**
     * @param mixed $startUid
     * @return $this
     */
    abstract function setStartUid($startUid);

    /**
     * @return mixed
     */
    abstract function getEndUid();

    /**
     * @param mixed $endUid
     * @return $this
     */
    abstract function setEndUid($endUid);

    /**
     * @return array
     */
    abstract function getUserIds();

    /**
     * @param array $userIds
     * @return $this
     */
    abstract function setUserIds(array $userIds);

    /**
     * @return mixed
     */
    abstract function getStartIp();

    /**
     * @param mixed $startIp
     * @return $this
     */
    abstract function setStartIp($startIp);

    /**
     * @return mixed
     */
    abstract function getEndIp();

    /**
     * @param mixed $endIp
     * @return $this
     */
    abstract function setEndIp($endIp);

    /**
     * @return array
     */
    abstract function getIps();

    /**
     * @param array $Ips
     * @return $this
     */
    abstract function setIps(array $Ips);


}