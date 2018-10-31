<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/2
 * Time: 下午11:13
 */

namespace Core\Http\Session;


/**
 * Class Base
 * @package Core\Http\Session
 */
class Base
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * Base constructor.
     */
    function __construct()
    {
        $this->session = Session::getInstance();
    }

    /**
     * @param null $name
     * @return bool
     */
    function sessionName($name = NULL)
    {
        return $this->session->sessionName($name);
    }

    /**
     * @param null $path
     * @return bool
     */
    function savePath($path = NULL)
    {
        return $this->session->savePath($path);
    }

    /**
     * @param null $sid
     * @return bool
     */
    function sessionId($sid = NULL)
    {
        return $this->session->sessionId($sid);
    }

    /**
     * @return bool
     */
    function destroy()
    {
        return $this->session->destroy();
    }

    /**
     * @return bool
     */
    function close()
    {
        return $this->session->close();
    }

    /**
     * @return bool
     */
    function start()
    {
        if (!$this->session->isStart()) {
            return $this->session->start();
        } else {
            trigger_error("session has start");
            return FALSE;
        }
    }
}