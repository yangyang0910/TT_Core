<?php
/**
 * Created by PhpStorm.
 * User: yangcai
 * Date: 2018/5/24
 * Time: 17:42
 */

namespace Core\AbstractInterface;

use Core\Conf\Config;
use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Message\Status;
use Core\Http\Message\ResponseJson;
use think\Template;

/**
 * http 控制器基类
 * Class AHttpController
 * @package Core\AbstractInterface
 */
class AHttpController extends ABaseController
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
     * 模板引擎
     * @var \think\Template
     */
    private $_templateEngine = null;

    /**
     *
     */
    function initialize()
    {
        parent::initialize();
        if (Config::getInstance()->getConf('TEMPLATE.enable')) {
            $this->_templateEngine = new Template(Config::getInstance()->getConf('TEMPLATE'));
        }
    }

    /**
     *
     */
    function index()
    {
        $this->actionNotFound();
    }

    /**
     * @return Request
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
     *
     */
    function responseError()
    {
        $this->response()->withStatus(Status::CODE_INTERNAL_SERVER_ERROR);
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
     *
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
     * 渲染模板文件
     * @access public
     * @param string $template 模板文件
     * @param array  $vars     模板变量
     * @param array  $config   模板参数
     * @return void
     */
    public function display($template, $vars = [], $config = [])
    {
        if ($this->_templateEngine) {
            // 由于ThinkPHP的模板引擎是直接echo输出到页面
            // 这里我们打开缓冲区，让模板引擎输出到缓冲区，再获取到模板编译后的字符串
            ob_start();
            $this->_templateEngine->fetch($template, $vars, $config);
            $content = ob_get_clean();
            $this->response()->write($content);
        }
    }

    /**
     * 模板变量赋值
     * @access public
     * @param mixed $name
     * @param mixed $value
     */
    public function assign($name, $value = '')
    {
        if ($this->_templateEngine) {
            $this->_templateEngine->assign($name, $value);
        }
    }
}