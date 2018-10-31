<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/27
 * Time: 下午10:56
 */

namespace Core\Component\RPC\Standard\Bean;


use Core\Component\Spl\SplBean;
use Core\Utility\Random;

class Response extends SplBean
{
    const STATUS_SERVICE_NOT_FOUND = 'SERVICE_NOT_FOUND';
    const STATUS_CONNECT_FAIL      = 'CONNECT_FAIL';
    const STATUS_SERVICE_ERROR     = 'SERVICE_ERROR';
    const STATUS_SERVICE_TIMEOUT   = 'SERVICE_TIMEOUT';
    const STATUS_PACKAGE_ERROR     = 'PACKAGE_ERROR';
    const STATUS_SERVICE_OK        = 'OK';

    const STATUS_RESPONSE_DETACH = 'RESPONSE_DETACH';//不响应客户端，可能是在异步时返回。
    protected $status;
    protected $result;
    protected $message;
    protected $responseId;

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getResponseId()
    {
        return $this->responseId;
    }

    /**
     * @param mixed $responseId
     */
    public function setResponseId($responseId)
    {
        $this->responseId = $responseId;
    }

    protected function initialize()
    {
        if (empty($this->responseId)) {
            $this->responseId = Random::character(10);
        }
    }

}