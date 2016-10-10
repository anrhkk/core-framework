<?php namespace core\kernel;

use Closure;
use ArrayAccess;
use Exception;
use ReflectionClass;

class Container implements ArrayAccess
{

    //绑定实例
    public $bindings = [];

    //单例服务
    public $instances = [];

    /*
     * 注册单例服务
     * @param $name 服务
     * @param $closure 闭包函数
     */
    public function single($name, $closure)
    {
        $this->bind($name, $closure, true);
    }

    /*
     * 服务绑定到容器
     * @param $name 服务名
     * @param $closure 返回服务对象的闭包函数
     * @param bool|false $force 是否单例
     */
    public function bind($name, $closure, $force = false)
    {
        $this->bindings[$name] = compact('closure', 'force');
    }

    /*
     * 单例服务
     * @param $name 名称
     * @param $object 对象
     */
    public function instance($name, $object)
    {
        $this->instances[$name] = $object;
    }

    public function offsetExists($key)
    {
        return isset($this->bindings[$key]);
    }

    public function offsetGet($key)
    {
        return $this->make($key);
    }

    /*
     * 获取服务实例
     * @param $name 服务名
     * @param bool|false $force 单例
     * @return Object
     * @throws Exception
     */
    public function make($name, $force = false)
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }
        //获得实现提供者
        $closure = $this->getClosure($name);

        //获取实例
        $object = $this->build($closure);

        //单例绑定
        if (isset($this->bindings[$name]['force']) && $this->bindings[$name]['force'] || $force) {
            $this->instances[$name] = $object;
        }

        return $object;
    }

    /*
     * 获得实例实现
     * @param $name 创建实例方式:类名或闭包函数
     * @return mixed
     */
    private function getClosure($name)
    {
        return isset($this->bindings[$name]) ? $this->bindings[$name]['closure'] : $name;
    }

    /*
     * 生成服务实例
     * @param $className 生成方式
     * @return object
     * @throws Exception
     */
    protected function build($className)
    {
        //匿名函数
        if ($className instanceof Closure) {
            //执行闭包函数
            return $className($this);
        }

        //获取类信息
        $reflector = new ReflectionClass($className);

        // 检查类是否可实例化, 排除抽象类abstract和对象接口interface
        if (!$reflector->isInstantiable()) {
            throw new Exception("$className 不能实例化.");
        }

        //获取类的构造函数
        $constructor = $reflector->getConstructor();

        //若无构造函数，直接实例化并返回
        if (is_null($constructor)) {
            return new $className;
        }

        //取构造函数参数,通过 ReflectionParameter 数组返回参数列表
        $parameters = $constructor->getParameters();

        //递归解析构造函数的参数
        $dependencies = $this->getDependencies($parameters);

        //创建一个类的新实例，给出的参数将传递到类的构造函数。
        return $reflector->newInstanceArgs($dependencies);
    }

    /*
     * 递归解析构造函数的参数
     * @param  构造函数参数 $parameters
     * @return array
     */
    protected function getDependencies($parameters)
    {
        $dependencies = [];

        //参数列表
        foreach ($parameters as $parameter) {
            //获取参数类型
            $dependency = $parameter->getClass();

            if (is_null($dependency)) {
                // 是变量,有默认值则设置默认值
                $dependencies[] = $this->resolveNonClass($parameter);
            } else {
                // 是一个类，递归解析
                $dependencies[] = $this->build($dependency->name);
            }
        }

        return $dependencies;
    }

    /*
     * 提取参数默认值
     * @param $parameter
     * @return mixed
     * @throws Exception
     */
    protected function resolveNonClass($parameter)
    {
        // 有默认值则返回默认值
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new Exception('参数无默认值，无法实例化');
    }

    public function offsetSet($key, $value)
    {
        if (!$value instanceof Closure) {
            $value = function () use ($value) {
                return $value;
            };
        }

        $this->bind($key, $value);
    }

    public function offsetUnset($key)
    {
        unset($this->bindings[$key], $this->instances[$key]);
    }

    /*
     * 魔术方法
     * @param $key
     * @return mixed|Object
     */
    public function __get($key)
    {
        return $this[$key];
    }

    /*
     * 魔术方法
     * @param [type] $key   [description]
     * @param [type] $value [description]
     */
    public function __set($key, $value)
    {
        $this[$key] = $value;
    }
}