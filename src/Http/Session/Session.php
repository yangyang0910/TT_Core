<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/2
 * Time: 下午9:25
 */

namespace Core\Http\Session;


use Core\Component\Di;
use Core\Component\SysConst;
use Core\Http\Request as HttpRequest;
use Core\Http\Response as HttpResponse;
use Core\Swoole\Task\TaskManager;
use Core\Utility\Random;

/**
 * Class Session
 * @package Core\Http\Session
 */
class Session
{
    /**
     * @var
     */
    private $sessionName;
    /**
     * @var
     */
    private $sessionSavePath;
    /**
     * @var bool
     */
    private $isStart = FALSE;
    /**
     * @var SessionHandler
     */
    private $sessionHandler;
    /**
     * @var
     */
    private $sessionId;
    /**
     * @var
     */
    private static $staticInstance;

    /**
     * @return Session
     */
    public static function getInstance()
    {
        if (!isset(self::$staticInstance)) {
            self::$staticInstance = new Session();
        }
        return self::$staticInstance;
    }

    /**
     * Session constructor.
     */
    function __construct()
    {
        $handler = Di::getInstance()->get(SysConst::SESSION_HANDLER);
        if ($handler instanceof \SessionHandlerInterface) {
            $this->sessionHandler = $handler;
        } else {
            $this->sessionHandler = new SessionHandler();
        }
        $this->init();
    }

    /**
     * @param null $name
     * @return bool
     */
    function sessionName($name = NULL)
    {
        if ($name == NULL) {
            return $this->sessionName;
        } else {
            if ($this->isStart) {
                trigger_error("your can not change session name as {$name} when session is start");
                return FALSE;
            } else {
                $this->sessionName = $name;
                return TRUE;
            }
        }
    }

    /**
     * @param null $sid
     * @return bool
     */
    function sessionId($sid = NULL)
    {
        if ($sid === NULL) {
            return $this->sessionId;
        } else {
            if ($this->isStart) {
                trigger_error("your can not change session sid as {$sid} when session is start");
                return FALSE;
            } else {
                $this->sessionId = $sid;
                return TRUE;
            }
        }
    }

    /**
     * @param null $path
     * @return bool
     */
    function savePath($path = NULL)
    {
        if ($path == NULL) {
            return $this->sessionSavePath;
        } else {
            if ($this->isStart) {
                trigger_error("your can not change session path as {$path} when session is start");
                return FALSE;
            } else {
                $this->sessionSavePath = $path;
                return TRUE;
            }
        }
    }

    /**
     * @return bool
     */
    function isStart()
    {
        return $this->isStart;
    }

    /**
     * @return bool
     */
    function start()
    {
        if (!$this->isStart) {
            $boolean = $this->sessionHandler->open($this->sessionSavePath, $this->sessionName);
            if (!$boolean) {
                trigger_error("session fail to open {$this->sessionSavePath} @ {$this->sessionName}");
                return FALSE;
            }
            $probability = intval(Di::getInstance()->get(SysConst::SESSION_GC_PROBABILITY));
            $probability = $probability >= 30 ? $probability : 1000;
            if (mt_rand(0, $probability) == 1) {
                $handler = clone $this->sessionHandler;
                TaskManager::getInstance()->add(function () use ($handler) {
                    $set = Di::getInstance()->get(SysConst::SESSION_GC_MAX_LIFE_TIME);
                    if (!empty($set)) {
                        $maxLifeTime = $set;
                    } else {
                        $maxLifeTime = 3600 * 24 * 30;
                    }
                    $handler->gc($maxLifeTime);
                });
            }
            $request = HttpRequest::getInstance();
            $cookie  = $request->getCookieParams($this->sessionName);
            if ($this->sessionId) {
                //预防提前指定sid
                if ($this->sessionId != $cookie) {
                    $data = array(
                        $this->sessionName => $this->sessionId
                    );
                    $request->withCookieParams($request->getRequestParam() + $data);
                    HttpResponse::getInstance()->setCookie($this->sessionName, $this->sessionId);
                }
            } else {
                if ($cookie === NULL) {
                    $sid  = $this->generateSid();
                    $data = array(
                        $this->sessionName => $sid
                    );
                    $request->withCookieParams($request->getRequestParam() + $data);
                    HttpResponse::getInstance()->setCookie($this->sessionName, $sid);
                    $this->sessionId = $sid;
                } else {
                    $this->sessionId = $cookie;
                }
            }
            $this->isStart = 1;
            return TRUE;
        } else {
            trigger_error('session has start');
            return FALSE;
        }
    }

    /**
     * @return bool
     */
    function close()
    {
        if ($this->isStart) {
            $this->init();
            return $this->sessionHandler->close();
        } else {
            return TRUE;
        }
    }

    /**
     *
     */
    private function init()
    {
        $name                  = Di::getInstance()->get(SysConst::SESSION_NAME);
        $this->sessionName     = $name ? $name : 'Swoole';
        $this->sessionSavePath = Di::getInstance()->get(SysConst::SESSION_SAVE_PATH);
        $this->sessionId       = NULL;
        $this->isStart         = FALSE;
    }

    /**
     * @return string
     */
    private function generateSid()
    {
        return md5(microtime() . Random::randStr(3));
    }

    /**
     * 当执行read的时候，要求上锁
     * @return string
     */
    function read()
    {
        return $this->sessionHandler->read($this->sessionId);
    }

    /**
     * @param $string
     * @return bool|int
     */
    function write($string)
    {
        return $this->sessionHandler->write($this->sessionId, $string);
    }

    /**
     * @return bool
     */
    function destroy()
    {
        if ($this->sessionHandler->destroy($this->sessionId)) {
            return $this->close();
        }
        return FALSE;
    }
}