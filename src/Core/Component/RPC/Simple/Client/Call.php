<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 下午5:40
 */

namespace Core\Component\RPC\Simple\Client;


use Core\Component\RPC\Simple\Common\Package;

/**
 * Class Call
 * @package Core\Component\RPC\Simple\Client
 */
class Call
{
    /**
     * @var Package
     */
    protected $package;
    /**
     * @var callable
     */
    protected $successCall;
    /**
     * @var callable
     */
    protected $failCall;

    /**
     * Call constructor.
     *
     * @param Package       $package
     * @param callable|NULL $success
     * @param callable|NULL $fail
     */
    function __construct(Package $package, callable $success = null, callable $fail = null)
    {
        $this->package     = $package;
        $this->successCall = $success;
        $this->failCall    = $fail;
    }

    /**
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return callable
     */
    public function getSuccessCall()
    {
        return $this->successCall;
    }

    /**
     * @return callable
     */
    public function getFailCall()
    {
        return $this->failCall;
    }
}