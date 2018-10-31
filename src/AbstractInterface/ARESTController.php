<?php

namespace Core\AbstractInterface;

use Core\Http\Message\Status;
use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Message\ResponseJson;

/*
 * 支持方法
    'GET',      // 从服务器取出资源（一项或多项）
    'POST',     // 在服务器新建一个资源
    'PUT',      // 在服务器更新资源（客户端提供改变后的完整资源）
    'PATCH',    // 在服务器更新资源（客户端提供改变的属性）
    'DELETE',   // 从服务器删除资源
    'HEAD',     // 获取 head 元数据
    'OPTIONS',  // 获取信息，关于资源的哪些属性是客户端可以改变的
 */

/**
 * restFul 控制器基类
 * Class ARESTController
 * @package Core\AbstractInterface
 */
abstract class ARESTController extends ABaseController
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;

    /**
     *
     */
    function initialize()
    {
        parent::initialize();
    }

    /**
     * @return mixed|void
     */
    function index()
    {
        $this->actionNotFound();
    }

    /**
     * @return Request|mixed
     */
    function request()
    {
        if (null === $this->request) {
            $this->request = Request::getInstance();
            $this->request->setExtendSpecification(Request::REST_SPECIFICATION);
        }
        return $this->request;
    }

    /**
     * @return Response|mixed
     */
    function response()
    {
        if (null === $this->response) {
            $this->response = Response::getInstance();
        }
        return $this->response;
    }

    /**
     * @return mixed|void
     */
    function responseError()
    {

    }

    /**
     * @return ResponseJson
     */
    function json()
    {
        return ResponseJson::getInstance($this->response());
    }

    /**
     * @param $actionName
     * @return mixed|void
     */
    protected function onRequest($actionName)
    {
    }

    /**
     * @return mixed|void
     */
    protected function afterAction()
    {
        try {
            $this->request()->hook()->event(
                $this->request()->getUri()->getPath(),
                $this->request(),
                $this->response()
            );
        } catch (\Exception $e) {

        }
    }

    /**
     * @param null $actionName
     * @param null $arguments
     * @return mixed|void
     */
    protected function actionNotFound($actionName = null, $arguments = null)
    {
        $this->response()->withStatus(Status::CODE_NOT_FOUND);
    }

    /**
     * @param $actionName
     * @param $arguments
     */
    function __call($actionName, $arguments)
    {
        /*
         * restful中无需预防恶意调用控制器内置方法。
         */
        $actionName = $this->request()->getMethod() . '_' . lcfirst($actionName);
        //执行onRequest事件
        $this->actionName($actionName);
        $this->onRequest($actionName);
        //判断是否被拦截
        if (!$this->response()->isEndResponse()) {
            $realName = $this->actionName();
            if (method_exists($this, $realName)) {
                $this->$realName();
            } else {
                $this->actionNotFound($realName, $arguments);
            }
        }
        $this->afterAction();
    }
}