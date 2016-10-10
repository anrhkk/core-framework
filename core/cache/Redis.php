<?php namespace core\cache;

use Exception;

/*
 * Redis缓存处理类
 * Class Redis
 * @package core\Cache
 */

class Redis implements InterfaceCache
{

    protected $obj;

    //连接
    public function connect()
    {
        $conf = Config::get('cache.redis');
        $this->obj = new Redis();

        if ($this->obj->connect($conf['host'], $conf['port'])) {
            throw new Exception("Redis 连接失败");
        }

        $this->obj->auth($conf['password']);
        $this->obj->select($conf['database']);
    }

    //设置
    public function set($name, $value, $expire = 3600)
    {

        if ($this->obj->set($name, $value)) {
            return $this->obj->expire($name, $expire);
        }
    }

    //获得
    public function get($name)
    {
        return $this->obj->get($name);
    }

    //删除
    public function del($name)
    {
        return $this->obj->del($name);
    }

    //清空缓存池
    public function delAll()
    {
        return $this->obj->clearall();
    }

    //清除缓存
    public function clear()
    {

    }
}