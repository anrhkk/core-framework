<?php namespace core\Cache;

/*
 * 缓存处理接口
 * Interface InterfaceCache
 * @package core\Cache
 */
interface InterfaceCache
{
    public function connect();

    public function set($name, $value, $expire);

    public function get($name);

    public function del($name);

    public function clear();
}