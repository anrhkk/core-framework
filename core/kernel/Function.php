<?php

/**
 * 跳转url
 * @param void $var
 */
function url($path, $args = [])
{
    if (empty($path) || preg_match('@^http@i', $path)) {
        return $path;
    }
    $path = explode('/', $path);
    $action = array_pop($path);
    $controller = array_pop($path);
    $url = __HOST__ . '/' . GROUP . '/' . $controller . '/' . $action;
    //添加参数
    if (!empty($args)) {
        $url .= '?' . http_build_query($args);
    }
    return $url;
}

/**
 * 调用 Api Server 支持调用类的静态方法
 * @param string $name 类名
 * @param string $method 方法名，如果为空则返回实例化对象
 * @param array $args 调用参数
 * @return object
 */
function api($class, $method = '', $args = [], $construct = [])
{
    static $_api = [];
    $identify = empty($args) ? $class . $method : $class . $method . md5(json_encode($args));
    if (!isset($_api[$identify])) {
        if (class_exists($class)) {
            $obj = new $class($construct);
            if (method_exists($obj, $method)) {
                if (!empty($args)) {
                    $_api[$identify] = call_user_func_array([&$obj, $method], $args);
                } else {
                    $_api[$identify] = $obj->$method();
                }
            } else {
                $_api[$identify] = $obj;
            }
        } else {
            error(lang('_CLASS_NOT_EXIST_'));
        }
    }
    return $_api[$identify];
}

/**
 * 打印输出数据|show的别名
 * @param void $var
 */
function dump($var, $echo = true)
{
    ob_start();
    var_dump($var);
    $output = ob_get_clean();
    $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
    $output = '<pre>' . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
    if ($echo) {
        echo($output);
        return null;
    } else {
        return $output;
    }
}


/**
 * 导入类库
 * @param  string $class 路径
 * @return bool
 */
function import($class)
{
    $file = str_replace(['@', '.', '#'], [APP_PATH, '/', '.'], $class);
    if (is_file($file)) {
        require_once $file;
        return true;
    } else {
        return false;
    }
}

/**
 * 跳转网址
 * @param string $url 跳转urlg
 * @param int $time 跳转时间
 * @param string $msg
 */
function redirect($url, $time = 0, $msg = '')
{
    if (!headers_sent()) {
        $time == 0 ? header("Location:" . $url) : header("refresh:{$time};url={$url}");
        exit($msg);
    } else {
        echo "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($msg) {
            echo($msg);
        }
        exit;
    }
}

/**
 * 打印用户常量
 */
function print_const()
{
    $d = get_defined_constants(true);
    dump($d['user']);
}

/**
 * 全局变量
 * @param $name 变量名
 * @param string $value 变量值
 * @return mixed 返回值
 */
function global_val($name, $value = '[null]')
{
    static $vars = [];
    $tmp = &$vars;
    //取变量
    if ($value == '[null]') {
        foreach (explode('.', $name) as $d) {
            if (isset($tmp[$d])) {
                $tmp = $tmp[$d];
            } else {
                return false;
            }
        }
        return $tmp;
    }
    //设置
    foreach (explode('.', $name) as $d) {
        if (!isset($tmp[$d])) {
            $tmp[$d] = [];
        }
        $tmp = &$tmp[$d];
    }
    return $tmp = $name;
}

/**
 * 获取语言
 * @param string $name
 * @param string $value
 */
function lang($name = '')
{
    return Lang::get($name);
}

/**
 * 驱动缓存
 * @param string $name 变量名
 * @param mixed $data 缓存数据
 * @param int $expire 过期时间 0　为持久缓存
 */
function cache($name, $data = '', $expire = null)
{
    if (empty($data)) {
        return Cache::get($name);
    } else {
        return Cache::set($name, $data, $expire);
    }
}

/**
 * 操作配置项
 * @param string $name
 * @param string $value
 */
function config($name = '', $value = '')
{
    if ($name === '') {
        return Config::all();
    }
    if ($value === '') {
        return Config::get($name);
    }
    return Config::set($name, $value);
}

/**
 * Session管理
 * @param        $name
 * @param string $value
 */
function session($name = '', $value = '[get]')
{
    $action = $value[0] == '[' ? trim($value, '[]') : 'set';
    return Session::$action($name, $value);
}

/*
 * Cookie管理
 * @param        $name
 * @param string $value
 */
function cookie($name, $value = '[get]')
{
    $action = $value[0] == '[' ? trim($value, '[]') : 'set';
    return Cookie::$action($name, $value);
}


/**
 * 抛出异常处理
 * @param string $msg 异常消息
 * @param integer $code 异常代码 默认为0
 * @return void
 */
function error($msg, $code = 0)
{
    throw new \Exception($msg, $code);
}

/**
 * 请求参数
 * @param $var 变量名
 * @param null $default 默认值
 * @param string $filter 数据处理函数
 * @return mixed
 */
function request($method, $var, $default = null, $filter = ['htmlspecialchars'])
{
    return Request::$method($var, $default, $filter);
}

/**
 * 返回值
 * @param $data 数组或提示
 * @param null $ajax true为成功，false
 * @param string $url 跳转链接
 * @param int $time 时间
 * @return mixed
 */
function response($data, $ajax = 2, $url = '', $time = 1000)
{
    if ($ajax == 1) {
        Response::ajaxSuccess($data, $url, $time);
    } else if ($ajax == 0) {
        Response::ajaxError($data, $url, $time);
    } else {
        Response::ajax($data);
    }
}

/**
 * 显示模板
 * @param $data 数组
 * @param string $view 模板
 * @param bool 显示或返回
 * @return mixed
 */
function view($view, array $data = [], $show = true)
{
    if ($show) {
        View::assign($data)->render($view);
    } else {
        return View::assign($data)->fetch($view);
    }
}

/*
 * trace 信息
 * @param  string $value 变量
 * @param  string $label 标签
 * @param  string $level 日志级别(或者页面Trace的选项卡)
 * @param  boolean $record 是否记录日志
 * @return void|array
 */
function trace($value = '[core]', $label = '', $level = 'DEBUG', $record = false)
{
    return Error::trace($value, $label, $level, $record);
}


/**
 * 字符串转换为数组，主要用于把分隔符调整到第二个参数
 * @param  string $str 要分割的字符串
 * @param  string $glue 分割符
 * @return array
 */
function str2arr($str, $glue = ',')
{
    return explode($glue, $str);
}

/**
 * 数组转换为字符串，主要用于把分隔符调整到第二个参数
 * @param  array $arr 要连接的数组
 * @param  string $glue 分割符
 * @return string
 */
function arr2str($arr, $glue = ',')
{
    return implode($glue, $arr);
}
