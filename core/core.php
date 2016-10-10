<?php
//版本号
define('APP_VERSION', '2.0.0');

//框架目录
define('CORE_PATH', __DIR__);

//项目根目录
define('ROOT_PATH', dirname(CORE_PATH));

//应用目录
defined('APP_PATH') or define('APP_PATH', ROOT_PATH . '/app');

//调试模式
defined("DEBUG") or define("DEBUG", false);

//系统类型
define('IS_CGI', substr(PHP_SAPI, 0, 3) == 'cgi' ? true : false);
define('IS_WIN', strstr(PHP_OS, 'WIN') ? true : false);
define('IS_CLI', PHP_SAPI == 'cli' ? true : false);
define('DS', DIRECTORY_SEPARATOR);

//请求类型
define('IS_GET', $_SERVER['REQUEST_METHOD'] == 'GET');
define('IS_POST', $_SERVER['REQUEST_METHOD'] == 'POST');
define('IS_DELETE', $_SERVER['REQUEST_METHOD'] == 'DELETE' ?: (isset($_POST['_method']) && $_POST['_method'] == 'DELETE'));
define('IS_PUT', $_SERVER['REQUEST_METHOD'] == 'PUT' ?: (isset($_POST['_method']) && $_POST['_method'] == 'PUT'));
define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

//时间信息
define('NOW', $_SERVER['REQUEST_TIME']);
define('NOW_TIME', microtime(true));

//路径信息
define('__HOST__', rtrim('http://' . $_SERVER['HTTP_HOST'] . preg_replace('@\w+\.php$@i', '', $_SERVER['SCRIPT_NAME']), '/'));
define('__URL__', 'http://' . $_SERVER['HTTP_HOST'] . '/' . trim($_SERVER['REQUEST_URI'], '/'));
define('__PUBLIC__', ROOT_PATH . '/public');
define("__HISTORY__", isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : null);

require CORE_PATH . '/kernel/Container.php';
require CORE_PATH . '/kernel/App.php';
require CORE_PATH . '/kernel/Function.php';

$app = new core\kernel\App();
(new core\kernel\Bootstrap($app));
