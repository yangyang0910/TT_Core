<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/22
 * Time: 下午9:54
 */

namespace Core;

use Core\Swoole\Server;
use Core\Conf\Config;
use Core\Conf\Event;
use Core\Component\Di;
use Core\Component\Error\Trigger;
use Core\Component\SysConst;
use Core\Http\Request;
use Core\Http\Response;
use Core\Utility\File;

/**
 * Class Core
 * @package Core
 */
class Core
{
    /**
     * @var
     */
    protected static $instance;

    /**
     * @var
     */
    private $preCall;

    /**
     * @var
     */
    private $appName;

    /**
     * @var
     */
    private $appEnv;

    /**
     * Core constructor.
     * @param $preCall
     */
    function __construct($preCall)
    {
        $this->preCall = $preCall;
    }

    /**
     * @param callable|null $preCall
     * @return Core
     */
    static function getInstance(callable $preCall = null)
    {
        if (!isset(self::$instance)) {
            self::$instance = new static($preCall);
        }
        return self::$instance;
    }

    function run()
    {
        Server::getInstance()->startServer();
    }

    /**
     * initialize frameWork
     * @param string $appName
     * @param string $appEnv
     * @return $this
     */
    function frameWorkInitialize($appName, $appEnv)
    {
        $this->appName = ucfirst($appName);
        $this->appEnv  = $appEnv;
        $this->defineSysConst();
        $this->registerAutoLoader();
        $this->preHandle();
        Event::getInstance()->frameInitialize();
        $this->sysDirectoryInit();
        Event::getInstance()->frameInitialized();
        $this->registerErrorHandler();
        return $this;
    }

    private function defineSysConst()
    {
        defined('ROOT') or define("ROOT", dirname(__DIR__, 3));
        defined('APP_NAME') or define("APP_NAME", $this->appName); // IDE 提示用
        defined('APP_ENV') or define("APP_ENV", $this->appEnv); // IDE 提示用
        defined('APP_ROOT') or define("APP_ROOT", ROOT . '/App/' . APP_NAME);
    }

    private static function registerAutoLoader()
    {
        $corePath = __DIR__;
        if ('/' !== ROOT) {
            $corePath = str_replace(ROOT, '', __DIR__);
        }
        require_once __DIR__ . "/AutoLoader.php";
        $loader = AutoLoader::getInstance();
        $loader->addNamespace("Core", $corePath);
        $loader->addNamespace(APP_NAME, 'App/' . APP_NAME);
        //添加系统依赖组件
        $loader->addNamespace("FastRoute", $corePath . "/Vendor/FastRoute");
        $loader->addNamespace("SuperClosure", $corePath . "/Vendor/SuperClosure");
        $loader->addNamespace("PhpParser", $corePath . "/Vendor/PhpParser");
        AutoLoader::getInstance()->requireFile('vendor/autoload.php');
    }

    private function sysDirectoryInit()
    {
        if (empty($tempDir = Di::getInstance()->get(SysConst::TEMP_DIRECTORY))) { //创建临时目录
            $tempDir = Config::getInstance()->getConf('APP_TEMP_DIR');
            Di::getInstance()->set(SysConst::TEMP_DIRECTORY, $tempDir);
        }
        if (!File::createDir($tempDir)) {
            die("create Temp Directory:{$tempDir} fail");
        } else {
            $path = $tempDir . "/Session"; // 创建默认Session存储目录
            File::createDir($path);
            Di::getInstance()->set(SysConst::SESSION_SAVE_PATH, $path);
        }
        if (empty($logDir = Di::getInstance()->get(SysConst::LOG_DIRECTORY))) { // 创建日志目录
            $logDir = Config::getInstance()->getConf('APP_LOG_DIR');
            Di::getInstance()->set(SysConst::LOG_DIRECTORY, $logDir);
        }
        if (!File::createDir($logDir)) {
            die("create log Directory:{$logDir} fail");
        }
        if (!$path = Config::getInstance()->getConf('SERVER.CONFIG.pid_file')) {
            Config::getInstance()->setConf("SERVER.CONFIG.pid_file", $logDir . "/server.pid");
        }
        if (!$path = Config::getInstance()->getConf('SERVER.CONFIG.log_file')) {
            Config::getInstance()->setConf("SERVER.CONFIG.log_file", $logDir . "/swoole.log");
        }
    }

    private function registerErrorHandler()
    {
        $conf = Config::getInstance()->getConf("APP_DEBUG");
        if ($conf['ENABLE'] == true) {
            ini_set("display_errors", "On");
            error_reporting(E_ALL | E_STRICT);
            set_error_handler(function ($errorCode, $description, $file = null, $line = null) {
                Trigger::error($description, $file, $line, $errorCode, debug_backtrace());
            });
            register_shutdown_function(function () {
                $error = error_get_last();
                if (!empty($error)) {
                    Trigger::error($error['message'], $error['file'], $error['line'], E_ERROR, debug_backtrace());
                    //HTTP下，发送致命错误时，原有进程无法按照预期结束链接,强制执行end
                    if (Request::getInstance()) {
                        Response::getInstance()->end(true);
                    }
                }
            });
        }
    }

    private function preHandle()
    {
        if (is_callable($this->preCall)) {
            call_user_func($this->preCall);
        }
        Di::getInstance()->set(SysConst::SESSION_NAME, Config::getInstance()->getConf('SITE_DOMAIN'));
        Di::getInstance()->set(SysConst::VERSION, '1.0');
    }
}