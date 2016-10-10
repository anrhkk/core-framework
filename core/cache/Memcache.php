<?php namespace core\cache;

use Exception;

/*
 * Memcache缓存处理类
 * Class Memcached
 * @package core\Cache
 */

class Memcached implements InterfaceCache
{

    protected $obj;

    public function __construct()
    {
        $this->connect();
    }

    //连接
    public function connect()
    {
        $conf = Config::get('cache.memcached');
        if ($this->obj = new Memcached()) {
            $this->obj->addServer($conf['host'], $conf['port']);
        } else {
            throw new Exception("Memcached 连接失败");
        }
    }

    //设置
    public function set($name, $value, $expire = 3600)
    {
        return $this->obj->set($name, $value, 0, $expire);
    }

    //获得
    public function get($name)
    {
        return $this->obj->get($name);
    }

    //删除
    public function del($name)
    {
        return $this->obj->delete($name);
    }

    //删除缓存池
    public function clear()
    {
        return $this->obj->clear();
    }

}