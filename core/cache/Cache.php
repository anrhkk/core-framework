<?php namespace core\cache;

/*
 * 缓存处理基类
 * Class Cache
 * @package core\Cache
 */
class Cache
{

    //应用
    protected $app;

    //连接
    protected $connect;

    public function __construct($app)
    {
        $this->app = $app;
        $driver = '\core\cache\\' . Config::get('cache.type');
        $this->connect = new $driver;
    }

    //更改缓存驱动
    public function driver($driver)
    {
        $driver = '\core\cache\\' . $driver;
        $this->connect = new $driver;

        return $this;
    }

    //缓存在本地
    public function store($name, $value = '[get]', $path = 'data/cache')
    {
        static $cache = [];
        $file = $path . '/' . $name . '.php';
        if ($value == '[del]') {
            if (is_file($file)) {
                unlink($file);
                if (isset($cache[$name])) {
                    unset($cache[$name]);
                }
            }
            return true;
        }
        if ($value === '[get]') {
            if (isset($cache[$name])) {
                return $cache[$name];
            } else if (is_file($file)) {
                return $cache[$name] = include $file;
            } else {
                return false;
            }
        }
        $data = "<?php if(!defined('CORE_PATH'))exit;\nreturn " . var_export($value, true) . ";\n?>";
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        if (!file_put_contents($file, $data)) {
            return false;
        }
        $cache[$name] = $value;
        return true;
    }

    /*
     * 生成静态
     * @param $controller 控制器
     * @param $action     动作
     * @param $file       静态文件
     * @return int
     */
    public function html($controller, $action, $file)
    {
        ob_start();
        $this->app->make($controller)->$action();
        $data = ob_get_clean();
        //目录检测
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }

        //创建静态文件
        return file_put_contents($file, $data) !== false;
    }

    public function __call($method, $params)
    {
        if (method_exists($this->connect, $method)) {
            return call_user_func_array([$this->connect, $method], $params);
        } else {
            return $this;
        }
    }

}