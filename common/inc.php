<?php
date_default_timezone_set('PRC');
@session_start();
define('ROOT_PATH', dirname(__DIR__));
define('DEBUG', 'on');
define("WEBPATH", str_replace("\\","/", ROOT_PATH));
define('COMMON_PATH', __DIR__);
define('LIB_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'lib'); //类库路径
define('HELPER_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'helpers'); //类库路径helpers
define('LOG_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'logs'); //日志路径
define('CONFIG_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'config'); //配置文件目录

//config
$configFiles = glob(CONFIG_PATH . '/*config.php');
foreach($configFiles as $file)
{
    require_once $file;
}

//helpers
require_once HELPER_PATH . '/functions.php';

//lib
require_once LIB_PATH . '/Swoole/Swoole.php';
require_once LIB_PATH . '/Swoole/Loader.php';

/**
 * 注册顶层命名空间到自动载入器
 */
Swoole\Loader::addNameSpace('Swoole', LIB_PATH . '/Swoole');
spl_autoload_register('\\Swoole\\Loader::autoload');

/**
 * 产生类库的全局变量
 */
global $php;
$php = Swoole::getInstance();