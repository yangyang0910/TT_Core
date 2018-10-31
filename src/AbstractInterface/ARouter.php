<?php

namespace Core\AbstractInterface;

use Core\Http\Request;
use Core\Http\Response;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;

/**
 * 路由基类
 * Class ARouter
 * @package Core\AbstractInterface
 */
abstract class ARouter
{
    /**
     * @var bool
     */
    protected $isCache = false;
    /**
     * @var
     */
    protected $cacheFile;
    /**
     * @var RouteCollector
     */
    private $routeCollector;

    /**
     * ARouter constructor.
     */
    function __construct()
    {
        $this->routeCollector = new RouteCollector(new Std(), new GroupCountBased());
        $this->register($this->routeCollector);
    }

    /**
     * @param RouteCollector $routeCollector
     * @return mixed
     */
    abstract function register(RouteCollector $routeCollector);

    /**
     * @return RouteCollector
     */
    function getRouteCollector()
    {
        return $this->routeCollector;
    }

    /**
     * @return Request
     */
    function request()
    {
        return Request::getInstance();
    }

    /**
     * @return Response
     */
    function response()
    {
        return Response::getInstance();
    }
}