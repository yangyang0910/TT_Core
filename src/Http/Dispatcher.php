<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午12:44
 */

namespace Core\Http;


use Core\Conf\Config;
use Core\Conf\Event;
use Core\AbstractInterface\ABaseController as Controller;
use Core\AbstractInterface\ARouter;
use Core\Component\Di;
use Core\Component\SysConst;
use Core\Http\Message\Status;
use FastRoute\Dispatcher\GroupCountBased;

/**
 * Class Dispatcher
 * @package Core\Http
 */
class Dispatcher
{
    /**
     * @var
     */
    protected static $selfInstance;
    /**
     * @var
     */
    protected $fastRouterDispatcher;
    /**
     * @var string
     */
    protected $controllerSuffix = 'Controller';
    /**
     * @var array
     */
    protected $controllerPool = [];
    /**
     * @var array|bool|mixed|null
     */
    protected $useControllerPool = false;
    /**
     * @var array
     */
    protected $controllerMap = [];
    /**
     * @var array
     */
    protected $serverParamMap = [];

    /**
     * @return Dispatcher
     */
    static function getInstance()
    {
        if (!isset(self::$selfInstance)) {
            self::$selfInstance = new Dispatcher();
        }
        return self::$selfInstance;
    }

    /**
     * Dispatcher constructor.
     */
    function __construct()
    {
        $this->useControllerPool = Config::getInstance()->getConf("CONTROLLER_POOL");
    }

    /**
     *
     */
    function dispatch()
    {
        if (Response::getInstance()->isEndResponse()) {
            return;
        }
        $httpMethod = Request::getInstance()->getMethod();
        $pathInfo   = UrlParser::pathInfo();
        $routeInfo  = $this->doFastRouter($pathInfo, $httpMethod);
        if ($routeInfo !== false) {
            switch ($routeInfo[0]) {
                case \FastRoute\Dispatcher::NOT_FOUND:
                    // ... 404 NdoDispatcherot Found
                    Response::getInstance()->withStatus(Status::CODE_NOT_FOUND);
                    break;
                case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    Response::getInstance()->withStatus(Status::CODE_METHOD_NOT_ALLOWED);
                    break;
                case \FastRoute\Dispatcher::FOUND:
                    $handler = $routeInfo[1];
                    $vars    = $routeInfo[2];
                    if (is_callable($handler)) {
                        call_user_func_array($handler, $vars);
                    } else {
                        if (is_string($handler)) {
                            $data = Request::getInstance()->getRequestParam();
                            Request::getInstance()->withQueryParams($vars + $data);
                            $pathInfo = UrlParser::pathInfo($handler);
                            Request::getInstance()->getUri()->withPath($pathInfo);
                        }
                    }
                    break;
            }
        }
        if (Response::getInstance()->isEndResponse()) {
            return;
        }
        $pathInfo = ltrim($pathInfo, "/");//去除为fastRouter预留的左边斜杠
        if (isset($this->controllerMap[$pathInfo])) {
            $finalClass = $this->controllerMap[$pathInfo]['finalClass'];
            $actionName = $this->controllerMap[$pathInfo]['actionName'];
        } else {
            if (count($this->controllerMap) > 50000) { //防止URL恶意攻击，造成Dispatch缓存爆满
                $this->controllerMap = [];
            }
            $list           = explode("/", $pathInfo);
            $controllerPath = APP_NAME . "\\Controller";
            $appName        = null;
            $controllerName = null;
            $actionName     = null;
            $finalClass     = null;
            if (preg_match('/v\d/', current($list))) { //版本控制
                $controllerPath .= ucfirst(array_shift($list));
                reset($list);
            }
            $controllerMaxDepth = Di::getInstance()->get(SysConst::CONTROLLER_MAX_DEPTH);
            if (intval($controllerMaxDepth) <= 0) {
                $controllerMaxDepth = 3;
            }
            $maxDepth = count($list) < $controllerMaxDepth ? count($list) : $controllerMaxDepth;
            while ($maxDepth > 0) {
                $className = '';
                for ($i = 0; $i < $maxDepth; $i++) {
                    if (strstr($list[$i], '_')) {
                        $words    = explode('_', $list[$i]);
                        $list[$i] = '';
                        foreach ($words as $k => $v) {
                            $list[$i] .= ucfirst($v);
                        }
                    }
                    $controllerName = ucfirst($list[$i]);
                    $className      = $className . "\\" . $controllerName;//为一级控制器Index服务
                }
                if ($this->controllerSuffix) {
                    $className .= $this->controllerSuffix;
                }
                if (class_exists($controllerPath . $className)) {
                    //尝试获取该class后的actionName
                    if (null === $actionName) {
                        $actionName = empty($list[$i]) ? 'index' : $list[$i];
                    }
                    $finalClass = $controllerPath . $className;
                    break;
                } else {
                    //尝试搜搜index控制器
                    $controllerName = $this->controllerSuffix ? 'Index' . $this->controllerSuffix : 'Index';
                    $temp           = $className . "\\" . $controllerName;
                    if (class_exists($controllerPath . $temp)) {
                        $finalClass = $controllerPath . $temp;
                        //尝试获取该class后的actionName
                        $actionName = empty($list[$i]) ? 'index' : $list[$i];
                        break;
                    }
                }
                $maxDepth--;
            }
            if (empty($finalClass)) {
                //若无法匹配完整控制器   搜搜Index控制器是否存在
                $controllerName = $this->controllerSuffix ? 'Index' . $this->controllerSuffix : 'Index';
                $finalClass     = $controllerPath . "\\" . $controllerName;
                $actionName     = empty($list[0]) ? 'index' : $list[0];
            }
            $this->controllerMap[$pathInfo]['finalClass'] = $finalClass;
            $this->controllerMap[$pathInfo]['actionName'] = $actionName;
            $this->controllerMap[$pathInfo]['httpMethod'] = $httpMethod;
        }
        if (class_exists($finalClass)) {
            if ($this->useControllerPool) {
                if (isset($this->controllerPool[$finalClass])) {
                    $controller = $this->controllerPool[$finalClass];
                } else {
                    $controller                        = new $finalClass;
                    $this->controllerPool[$finalClass] = $controller;
                }
            } else {
                $controller = new $finalClass;
            }
            if ($controller instanceof Controller) {
                // 事件
                if (false === Event::getInstance()->onDispatcher(Request::getInstance(), Response::getInstance(),
                        $finalClass, $actionName)) {
                    return;
                }
                // 用作权限验证
                if (Status::CODE_FORBIDDEN === Response::getInstance()->getStatusCode()) {
                    Response::getInstance()->withStatus(Status::CODE_OK);
                    return;
                }
                // 预防在进控制器之前已经被拦截处理
                if (!Response::getInstance()->isEndResponse()) {
                    $controller->__call($actionName, null);
                }
            } else {
                Response::getInstance()->withStatus(Status::CODE_NOT_FOUND);
                trigger_error("controller {$finalClass} is not a instance of ABaseController");
            }
        } else {
            Response::getInstance()->withStatus(Status::CODE_NOT_FOUND);
            trigger_error("default controller Index not implement");
        }
    }

    /**
     * @param $pathInfo
     * @param $requestMethod
     * @return array|bool
     */
    private function doFastRouter($pathInfo, $requestMethod)
    {
        if (!isset($this->fastRouterDispatcher)) {
            $this->intRouterInstance();
        }
        if ($this->fastRouterDispatcher instanceof GroupCountBased) {
            return $this->fastRouterDispatcher->dispatch($requestMethod, $pathInfo);
        } else {
            return false;
        }
    }

    /**
     *
     */
    private function intRouterInstance()
    {
        try {
            /*
             * if exit Router class in App directory
             */
            $ref    = new \ReflectionClass(APP_NAME . "\\Router");
            $router = $ref->newInstance();
            if ($router instanceof ARouter) {
                $this->fastRouterDispatcher = new GroupCountBased($router->getRouteCollector()->getData());
            }
        } catch (\Exception $exception) {
            //没有设置路由
            $this->fastRouterDispatcher = false;
        }
    }

}