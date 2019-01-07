<?php
/**
 * Created by PhpStorm.
 * User: safer
 * Date: 2018/6/5
 * Time: 22:50
 */

namespace Core\Utility\Logic;

/**
 * Class Response
 * @package Core\Utility\Logic\ALogic
 */
class Response
{
    protected $data = null;
    protected $page = null;
    protected $msg = 'success';
    protected $status = true;
    protected $code = 0;
    /**
     * @var ACodePhrase
     */
    static protected $codePhrase;

    private static $instance;

    static function getInstance()
    {
//        if (!self::$instance) {
//            self::$instance = new self();
//        }
//        return self::$instance;
        return new self();
    }

    /**
     * @return null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param null $data
     *
     * @return Response
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param null $key
     *
     * @return null
     */
    public function getPage($key = null)
    {
        if (null === $key) {
            return $this->page;
        }
        if (!isset($this->page[$key])) {
            return null;
        }
        return $this->page[$key];
    }

    /**
     * @param null $page
     *
     * @return Response
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return null
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * @param null $msg
     *
     * @return Response
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;
        return $this;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     *
     * @return Response
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @param $codePhrase
     */
    static public function setCodePhrase($codePhrase)
    {
        self::$codePhrase = $codePhrase;
    }

    /**
     * @return ACodePhrase
     */
    public function getCodePhrase()
    {
        return self::$codePhrase;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return Response
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function success($data = null, $msg = null, $code = null)
    {
        if ($data) {
            $this->setData($data);
        }
        if ($msg) {
            $this->setMsg($msg);
        }
        if ($code) {
            $this->setCode($code);
        }
        $this->setStatus(true);
        return $this->_send();
    }

    public function error($msg = null, $code = null)
    {
        if ($msg) {
            $this->setMsg($msg);
        }
        if ($code) {
            $this->setCode($code);
        }
        $this->setStatus(false);
        return $this->_send();
    }

    private function _send()
    {
        if (self::getCodePhrase() && $this->getCode()) {
            $codePhrase = self::$codePhrase;
            $this->setMsg($codePhrase::getReasonPhrase($this->getCode()));
        }
        return $this;
    }
}