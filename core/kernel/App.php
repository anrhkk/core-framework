<?php namespace core\kernel;

use ReflectionClass;

class App extends Container
{
    //应用已启动
    protected $booted = false;

    //服务配置
    protected $config = [];

    //外观别名
    protected $facades = [];

    //延迟加载服务提供者
    protected $deferProviders = [];

    //已加载服务提供者
    protected $serviceProviders = [];

    //类库映射
    protected $alias = [];

    //构造函数
    public function __construct()
    {
        //注册自动载入函数
        spl_autoload_register([$this, 'autoload']);
        spl_autoload_register([$this, 'autoloadFacade']);

        //引入服务配置
        $this->config = require APP_PATH . '/service/index.php';

        //绑定核心服务提供者
        $this->bindServiceProvider();

        //添加初始实例
        $this->instance('App', $this);

        //设置外观基类APP属性
        ServiceFacade::setFacadeApplication($this);

        //导入类库别名
        $this->addMap(Config::get('app.alias'));

        //启动
        $this->boot();
    }

    /*
     * 服务加载处理
     */
    public function bindServiceProvider()
    {
        foreach ($this->config['providers'] as $provider) {
            $reflectionClass = new ReflectionClass($provider);
            $properties = $reflectionClass->getDefaultProperties();

            //获取服务延迟属性
            if (isset($properties['defer']) && $properties['defer']) {
                $alias = substr($reflectionClass->getShortName(), 0, -8);

                //延迟加载服务
                $this->deferProviders[$alias] = $provider;
            } else {
                //立即加载服务
                $this->register(new $provider($this));
            }
        }
    }

    /*
     * 注册服务
     * @param  [type] $provider [description]
     * @return [type]           [description]
     */
    public function register($provider)
    {
        //服务对象已经注册过时直接返回
        if ($registered = $this->getProvider($provider)) {
            return $registered;
        }

        if (is_string($provider)) {
            $provider = new $provider($this);
        }

        $provider->register($this);

        //记录服务
        $this->serviceProviders[] = $provider;

        if ($this->booted) {
            $this->bootProvider($provider);
        }
    }

    /*
     * 获取已经注册的服务
     * @param  [type] $provider [description]
     * @return [type]           [description]
     */
    protected function getProvider($provider)
    {
        $class = is_object($provider) ? get_class($provider) : $provider;
        foreach ($this->serviceProviders as $value) {
            if ($value instanceof $class) {
                return $value;
            }
        }
    }

    /*
     * 运行服务提供者的boot方法
     * @param [type] $provider [description]
     */
    protected function bootProvider($provider)
    {
        if (method_exists($provider, 'boot')) {
            $provider->boot();
        }
    }

    /*
     * 类库映射
     * @param array|string $alias 别名
     * @param string $namespace 命名空间
     */
    protected function addMap($alias, $namespace = '')
    {
        if (is_array($alias)) {
            foreach ($alias as $key => $value) {
                $this->alias[$key] = $value;
            }
        } else {
            $this->alias[$alias] = $namespace;
        }
    }

    /*
     * 系统启动
     * @return void
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }
        foreach ($this->serviceProviders as $p) {
            $this->bootProvider($p);
        }
        $this->booted = true;
    }

    /*
     * 获取服务对象
     * @param $name
     * @param bool|false $force
     * @return Object
     */
    public function make($name, $force = false)
    {
        if (isset($this->deferProviders[$name])) {
            $this->register(new $this->deferProviders[$name]($this));
            unset($this->deferProviders[$name]);
        }
        return parent::make($name, $force);
    }

    /*
     * 类自动加载
     * @param $class
     */
    public function autoload($class)
    {
        $file = str_replace('\\', DS, $class) . '.php';
        if (isset($this->alias[$class])) {
            //检测类库映射
            require_once str_replace('\\', DS, $this->alias[$class]);
        } else if (is_file(ROOT_PATH . DS . $file)) {
            //直接加载文件
            require_once ROOT_PATH . DS . $file;
        } else if (defined('APP_PATH') && is_file(APP_PATH . DS . $file)) {
            //项目文件
            require_once APP_PATH . DS . $file;
        } else if (class_exists('Config', false)) {
            //自动加载命名空间
            foreach ((array)Config::get('app.autoload_namespace') as $key => $value) {
                if (strpos($class, $key) !== false) {
                    $file = str_replace($key, $value, $class) . '.php';
                    require_once(str_replace('\\', DS, $file));
                }
            }
        }
    }

    /*
     * 自动加载facade类
     * @param $class
     * @return bool
     */
    public function autoloadFacade($class)
    {
        $file = str_replace('\\', '/', $class);
        $facade = basename($file);
        if (isset($this->config['facades'][$facade])) {
            //加载facade类
            return class_alias($this->config['facades'][$facade], $class);
        }
    }
}