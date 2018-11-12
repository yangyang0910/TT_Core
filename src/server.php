<?php

date_default_timezone_set('Asia/Shanghai');

if (false === extension_loaded('swoole')) {
    exit("Please install swoole extension.\n");
}
if ('cli' !== php_sapi_name()) {
    exit("Please use php cli mode.\n");
}
function commandParser()
{
    global $argv;
    $argv    = array_map('trim', $argv);
    $argv    = array_unique($argv);
    $command = array_intersect(['start', 'stop', 'reload', 'cron', '-h'], $argv);
    if (false === $command = current($command)) {
        exit("command not found.\n");
    }
    unset($argv[array_search($command, $argv)]);
    reset($argv);
    $options = [];
    foreach ($argv as $item) {
        if (substr($item, 0, 2) === '--') {
            $temp          = trim($item, "--");
            $temp          = explode("-", $temp);
            $key           = array_shift($temp);
            $options[$key] = array_shift($temp) ?: '';
        }
    }
    return [
        "command" => $command,
        "options" => $options,
    ];
}

function commandHandler()
{
    $command = commandParser();
    if ('-h' === $command['command']) {
        help();
        exit();
    }
    if (false === isset($command['options'])) {
        exit("options not found.\n");
    }
    if (false === isset($command['options']['app'])) {
        exit("app name not found.\n");
    }
    if (false === isset($command['options']['env'])) {
        exit("operating environment not found.\n");
    }
    require_once 'Core.php';
    define('APP_RUN_MODE', 'cron' === $command['command'] ? 'cron' : 'server');
    global $server;
    $server = \Core\Core::getInstance()->frameWorkInitialize($command['options']['app'], $command['options']['env']);
    if ('cron' === APP_RUN_MODE) {
        define('CRON_SREVER_ROUTE', $command['options']['route']);
        $server->run();
        return;
    }
    switch ($command['command']) {
        case "start":
            startServer($command['options']);
            break;
        case 'stop':
            stopServer($command['options']);
            break;
        case 'reload':
            reloadServer($command['options']);
            break;
        case 'version':
            echo \Core\Component\Di::getInstance()->get(\Core\Component\SysConst::VERSION) . PHP_EOL;
            break;
        default:
            exit("command not found.\n");
    }
}

function startServer($options)
{

    $log = "
           _____              _____
    _________  /______ _________  /_
    __  ___/  __/  __ `/_  ___/  __/
    _(__  )/ /_ / /_/ /_  /   / /_
    /____/ \__/ \__,_/ /_/    \__/        Thanks PHP and Swoole.

_.------------------------------------------------------------------._
\n";

    $now_date = date('Y-m-d H:i:s');
    $log      .= "              stat time: {$now_date} \n";
    echo $log . PHP_EOL;
    opCacheClear();
    /** @var \Core\Core $server */
    global $server;
    $conf = \Core\Conf\Config::getInstance();
    if (isset($options['app'])) {
        $conf->setConf("SERVER.APP_NAME", $options['app']);
    }
    echo '  app name              ' . $conf->getConf('SERVER.APP_NAME') . PHP_EOL;
    if (isset($options['env'])) {
        $conf->setConf("SERVER.ENV", $options['env']);
    }
    echo '  app running env       ' . $conf->getConf('SERVER.ENV') . PHP_EOL;
    echo PHP_EOL;
    echo '  running user          ' . $conf->getConf('SERVER.CONFIG.user') . PHP_EOL;
    if (isset($options['group'])) {
        $conf->setConf("SERVER.CONFIG.group", $options['group']);
    }
    echo '  running user group     ' . $conf->getConf('SERVER.CONFIG.group') . PHP_EOL;
    if (isset($options['cpuAffinity'])) {
        $conf->setConf("SERVER.CONFIG.open_cpu_affinity", true);
    }
    echo PHP_EOL;
    if (isset($options['ip'])) {
        $conf->setConf("SERVER.LISTEN", $options['ip']);
    }
    echo '  listen address        ' . $conf->getConf('SERVER.LISTEN') . PHP_EOL;
    if (!empty($options['p'])) {
        $conf->setConf("SERVER.PORT", $options['p']);
    }
    echo '  listen port           ' . $conf->getConf('SERVER.PORT') . PHP_EOL;
    if (!empty($options['pid'])) {
        $pidFile = $options['pid'];
        \COre\Conf\Config::getInstance()->setConf("SERVER.CONFIG.pid_file", $pidFile);
    }
    if (isset($options['workerNum'])) {
        $conf->setConf("SERVER.CONFIG.worker_num", $options['workerNum']);
    }
    echo '  worker num            ' . $conf->getConf('SERVER.CONFIG.worker_num') . PHP_EOL;
    if (isset($options['taskWorkerNum'])) {
        $conf->setConf("SERVER.CONFIG.task_worker_num", $options['taskWorkerNum']);
    }
    echo '  task worker num       ' . $conf->getConf('SERVER.CONFIG.task_worker_num') . PHP_EOL;
    if (isset($options['user'])) {
        $conf->setConf("SERVER.CONFIG.user", $options['user']);
    }
    $label = 'false';
    if (isset($options['d'])) {
        $conf->setConf("SERVER.CONFIG.daemonize", true);
        $label = 'true';
    } else {
        \Core\Conf\Config::getInstance()->setConf("SERVER.CONFIG.pid_file", null);
    }
    echo '  daemonize             ' . $label . PHP_EOL;
    $label = 'false';
    if ($conf->getConf('APP_DEBUG.ENABLE')) {
        $label = 'true';
    }
    echo '  debug enable          ' . $label . PHP_EOL;
    $label = 'false';
    if ($conf->getConf('APP_DEBUG.LOG')) {
        $label = 'true';
    }
    echo '  debug log error       ' . $label . PHP_EOL;
    $label = 'false';
    if ($conf->getConf('APP_DEBUG.DISPLAY_ERROR')) {
        $label = 'true';
    }
    echo '  debug display error   ' . $label . PHP_EOL;
    echo PHP_EOL;
    echo '  php version           ' . phpversion() . PHP_EOL;
    echo '  swoole version        ' . phpversion('swoole') . PHP_EOL;
    echo PHP_EOL;
    $server->run();

}

function stopServer($options)
{
    $pidFile = \Core\Conf\Config::getInstance()->getConf("SERVER.CONFIG.pid_file");
    if (!empty($options['pid'])) {
        $pidFile = $options['pid'];
    }
    if (!file_exists($pidFile)) {
        echo "pid file :{$pidFile} not exist \n";
        return;
    }
    $pid = file_get_contents($pidFile);
    if (!swoole_process::kill($pid, 0)) {
        echo "pid :{$pid} not exist \n";
        return;
    }
    if (isset($options['f'])) {
        swoole_process::kill($pid, SIGKILL);
    } else {
        swoole_process::kill($pid);
    }
    //等待两秒
    $time = time();
    while (true) {
        usleep(1000);
        if (!swoole_process::kill($pid, 0)) {
            echo "server stop at " . date("y-m-d h:i:s") . PHP_EOL;
            if (is_file($pidFile)) {
                unlink($pidFile);
            }
            break;
        } else {
            if (time() - $time > 2) {
                echo "stop server fail.try --force again \n";
                break;
            }
        }
    }
}

function reloadServer($options)
{
    $pidFile = \Core\Conf\Config::getInstance()->getConf("SERVER.CONFIG.pid_file");
    if (isset($options['pid'])) {
        if (!empty($options['pid'])) {
            $pidFile = $options['pid'];
        }
    }
    if (isset($options['all']) && $options['all'] == false) {
        $sig = SIGUSR2;
    } else {
        $sig = SIGUSR1;
    }
    if (!file_exists($pidFile)) {
        echo "pid file :{$pidFile} not exist \n";
        return;
    }
    $pid = file_get_contents($pidFile);
    opCacheClear();
    if (!swoole_process::kill($pid, 0)) {
        echo "pid :{$pid} not exist \n";
        return;
    }
    swoole_process::kill($pid, $sig);
    echo "send server reload command at " . date("y-m-d h:i:s") . PHP_EOL;
}


function help()
{
    echo PHP_EOL;
    echo "------------ 启动命令 ------------\n";
    echo PHP_EOL;
    echo "执行【 php App/bin/swoole_server --app-appName --env-envName start】 即可启动服务。启动可选参数为:\n";
    echo PHP_EOL;
    echo "  --d                       是否以系统守护模式运行\n";
    echo "  --app-appName             指定 app \n";
    echo "  --env-envName             指定运行配置环境 【 dev、test、pre、pro 】\n";
    echo "  --p-portNumber            指定服务监听端口\n";
    echo "  --pid-fileName            指定服务PID存储文件\n";
    echo "  --workerNum-num           设置worker进程数\n";
    echo "  --taskWorkerNum-num       设置Task进程数\n";
    echo "  --user-userName           指定以某个用户身份执行\n";
    echo "  --group-groupName         指定以某个用户组身份执行\n";
    echo "  --taskWorkerNum-num       设置Task进程数\n";
    echo "  --cpuAffinity-boolean     是否开启CPU亲和\n";
    echo PHP_EOL;
    echo "------------ 停止命令 ------------\n";
    echo PHP_EOL;
    echo "执行【 php App/bin/swoole_server --app-appName stop】 即可启动服务。停止可选参数为:\n";
    echo PHP_EOL;
    echo "  --app-appName             指定 app \n";
    echo "  --pid-fileName            指定服务PID存储文件\n";
    echo "  --f                       强制停止服务\n";
    echo PHP_EOL;
    echo "------------ 重启命令 ------------\n";
    echo PHP_EOL;
    echo "执行【 php App/bin/swoole_server --app-appName reload】 即可重启可服务。重启可选参数为:\n";
    echo PHP_EOL;
    echo "  --app-appName             指定 app \n";
    echo "  --pid-fileName            指定服务PID存储文件\n";
    echo "  --pid-all                 是否重启所有进程，默认true\n";
    echo PHP_EOL;
}

function evenCheck()
{
    if (version_compare(phpversion(), '5.6', '<')) {
        die("php version must >= 5.6");
    }
    if (version_compare(phpversion('swoole'), '1.9.5', '<')) {
        die("swoole version must >= 1.9.5");
    }
}

function opCacheClear()
{
    if (function_exists('apc_clear_cache')) {
        apc_clear_cache();
    }
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
}

evenCheck();
commandHandler();
