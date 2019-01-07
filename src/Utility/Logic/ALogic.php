<?php
/**
 * Created by PhpStorm.
 * User: yangcai
 * Date: 2018/5/16
 * Time: 19:02
 */

namespace Core\Utility\Logic;

use Core\Component\Di;

/**
 * logic 基类
 * Class ALogic
 * @package Core\Utility\Logic\ALogic
 */
abstract class ALogic
{
    /**
     * @var Di
     */
    static protected $di;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;

    /**
     * ALogic constructor.
     */
    function __construct()
    {
        self::$di       = Di::getInstance();
        $this->request  = Request::getInstance();
        $this->response = Response::getInstance();
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
     * @return Request
     */
    function request()
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    function response()
    {
        return $this->response;
    }

    /**
     * @param $actionName
     *
     * @return Response
     */
    function actionNotFound($actionName)
    {
        return $this->response()->error($actionName . ' method not found');
    }

    /**
     * @param string $actionName
     *
     * @return Response
     */
    function call($actionName)
    {
        if (!method_exists($this, $actionName)) {
            return $this->actionNotFound($actionName);
        }
        $eventActions = ['getList', 'getInfo', 'create', 'update', 'delete'];
        if (\in_array($actionName, $eventActions, true)) {
            $eventBeforeActionName = '_EVENT_before' . ucfirst($actionName);
            if (method_exists($this, $eventBeforeActionName)) {
                $this->$eventBeforeActionName();
            }
        }
        $response = $this->$actionName();
        if (\in_array($actionName, $eventActions, true)) {
            $eventAfterActionName = '_EVENT_after' . ucfirst($actionName);
            if (method_exists($this, $eventAfterActionName)) {
                $this->$eventAfterActionName();
            }
        }
        return $response;
    }
}