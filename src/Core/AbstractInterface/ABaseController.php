<?php

namespace Core\AbstractInterface;

use Core\Component\Di;

/**
 * 控制器基类
 * Class ABaseController
 * @package Core\AbstractInterface
 */
abstract class ABaseController
{
    /**
     * @var Di
     */
    static protected $di;
    /**
     * @var null
     */
    protected $actionName = null;
    /**
     * @var null
     */
    protected $callArgs = null;

    function __construct()
    {
        self::$di = Di::getInstance();
        $this->initialize();
    }

    /**
     *
     */
    function initialize()
    {
    }

    /**
     * @return Di
     */
    static final protected function di()
    {
        return self::$di;
    }

    /**
     * @return array
     */
    function getPageData()
    {
        /* 分页 */
        $pageNumber = $this->request()->getQueryParam('p_num');
        $pageLimit  = $this->request()->getQueryParam('p_limit');
        $pageTotal  = $this->request()->getQueryParam('p_total');
        $pageParams = [
            'p_num'   => (int)$pageNumber,
            'p_limit' => (int)$pageLimit,
            'p_start' => (int)($pageNumber - 1) * $pageLimit,
            'p_total' => (int)$pageTotal,
        ];
        return $pageParams;
    }

    /**
     * @param null $actionName
     *
     * @return null
     */
    function actionName($actionName = null)
    {
        if ($actionName === null) {
            return $this->actionName;
        } else {
            $this->actionName = $actionName;
        }
    }

    /**
     * @return mixed
     */
    abstract function index();

    /**
     * @param $actionName
     *
     * @return mixed
     */
    abstract protected function onRequest($actionName);

    /**
     * @param null $actionName
     * @param null $arguments
     *
     * @return mixed
     */
    abstract protected function actionNotFound($actionName = null, $arguments = null);

    /**
     * @return mixed
     */
    abstract protected function afterAction();

    /**
     * @return mixed
     */
    abstract function request();

    /**
     * @return mixed
     */
    abstract function response();

    /**
     * @return mixed
     */
    abstract function responseError();

    /**
     * @param $actionName
     * @param $arguments
     */
    function __call($actionName, $arguments)
    {
        /*
           * 防止恶意调用
           * actionName、onRequest、actionNotFound、afterAction、request
           * response、__call
        */
        if (in_array($actionName, [
            'actionName',
            'onRequest',
            'actionNotFound',
            'afterAction',
            'request',
            'response',
            '__call',
        ])) {
            $this->responseError();
            return;
        }
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