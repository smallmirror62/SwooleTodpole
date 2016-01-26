<?php if (!defined('ROOT_PATH')) die('Deny!');
function logs($arg, $logName = 'debug')
{

    $logName = $logName ? $logName : 'wanglibao';//日志名称
    $fp = fopen(LOG_PATH . '/' . $logName . '.log.' . date('Ymd'), 'a');

    $traces = debug_backtrace();
    $logMsg = 'FILE:' . basename($traces[0]['file']) . PHP_EOL;
    $logMsg .= 'FUNC:' . $traces[1]['function'] . PHP_EOL;
    $logMsg .= 'LINE:' . $traces[0]['line'] . PHP_EOL;

    if (is_string($arg)) {
        $logMsg .= 'ARGS:' . $arg . PHP_EOL;
    } else {
        $logMsg .= 'ARGS:' . var_export($arg, true) . PHP_EOL;
    }
    $logMsg .= 'DATETIME:' . date('Y-m-d H:i:s') . PHP_EOL . PHP_EOL;

    fwrite($fp, $logMsg);
    fclose($fp);
}

/**
 * 错误信息输出处理
 */
function swoole_error_handler($errno, $errstr, $errfile, $errline)
{
    $info = '';
    switch ($errno)
    {
        case E_USER_ERROR:
            $level = 'User Error';
            break;
        case E_USER_WARNING:
            $level = 'Warnning';
            break;
        case E_USER_NOTICE:
            $level = 'Notice';
            break;
        default:
            $level = 'Unknow';
            break;
    }

    $title = 'Swoole '.$level;
    $info .= '<b>File:</b> '.$errfile."<br />\n";
    $info .= '<b>Line:</b> '.$errline."<br />\n";
    $info .= '<b>Info:</b> '.$errstr."<br />\n";
    $info .= '<b>Code:</b> '.$errno."<br />\n";
    echo Swoole\Error::info($title, $info);
}

/**
 * 引发一个错误
 * @param $error_id
 * @param $stop
 */
function error($error_id, $stop = true)
{
    global $php;
    $error = new \Swoole\Error($error_id);
    if (isset($php->error_call[$error_id]))
    {
        call_user_func($php->error_call[$error_id], $error);
    }
    elseif ($stop)
    {
        exit($error);
    }
    else
    {
        echo $error;
    }
}

/**
 * 调试数据，终止程序的运行
 */
function debug()
{
    $vars = func_get_args();
    foreach ($vars as $var)
    {
        if (php_sapi_name() == 'cli')
        {
            var_export($var);
        }
        else
        {
            highlight_string("<?php\n" . var_export($var, true));
            echo '<hr />';
        }
    }
    exit;
}

function createModel($model_name)
{
    return model($model_name);
}

/**
 * 生产一个model接口，模型在注册树上为单例
 * @param $model_name
 * @param $db_key
 * @return Swoole\Model
 */
function model($model_name, $db_key = 'master')
{
    return Swoole::getInstance()->model->loadModel($model_name, $db_key);
}

/**
 * 传入一个数据库表，返回一个封装此表的Model接口
 * @param $table_name
 * @param $db_key
 * @return Swoole\Model
 */
function table($table_name, $db_key = 'master')
{
    return Swoole::getInstance()->model->loadTable($table_name, $db_key);
}

/**
 * 开启会话
 * @param $readonly
 */
function session($readonly = false)
{
    Swoole::getInstance()->session->start($readonly);
}