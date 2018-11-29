<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/11/22
 * Time: 下午9:57
 */

namespace Core\Component\Version;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\Dispatcher\GroupCountBased as Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;


/**
 * Class Version
 * @package Core\Component\Version
 */
class Version
{
    /**
     * @var
     */
    private $versionName;
    /**
     * @var callable
     */
    private $judge;
    /**
     * @var RouteCollector
     */
    private $routeCollector;
    /**
     * @var null
     */
    private $dispatcher = null;
    /**
     * @var null
     */
    private $defaultHandler = null;

    /**
     * Version constructor.
     *
     * @param          $versionName
     * @param callable $judge
     */
    function __construct($versionName, callable $judge)
    {
        $this->versionName    = $versionName;
        $this->judge          = $judge;
        $this->routeCollector = new RouteCollector(new Std(), new GroupCountBased());
    }

    /**
     * @return RouteCollector
     */
    function register()
    {
        return $this->routeCollector;
    }

    /**
     * @param $urlPath
     * @param $requestMethod
     *
     * @return array
     */
    function dispatch($urlPath, $requestMethod)
    {
        if ($this->dispatcher == null) {
            $this->dispatcher = new Dispatcher($this->routeCollector->getData());
        }
        return $this->dispatcher->dispatch($requestMethod, $urlPath);
    }

    /**
     * @return mixed
     */
    public function getVersionName()
    {
        return $this->versionName;
    }

    /**
     * @return callable
     */
    public function getJudge()
    {
        return $this->judge;
    }

    /**
     * @return null
     */
    public function getDefaultHandler()
    {
        return $this->defaultHandler;
    }


}