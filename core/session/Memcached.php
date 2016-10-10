<?php namespace core\session;

class Memcached implements SessionInterface
{

    private $memcached;

    function __construct()
    {
    }

    public function make()
    {
        $options = Config('session.memcached');
        $this->memcached = new Memcached();
        $this->memcached->connect($options['host'], $options['port']);
        session_set_save_handler(
            [&$this, "open"],
            [&$this, "close"],
            [&$this, "read"],
            [&$this, "write"],
            [&$this, "destroy"],
            [&$this, "gc"]
        );
    }

    public function open()
    {
        return true;
    }

    /*
     * 获得缓存数据
     * @param string $sid
     * @return boolean
     */
    public function read($sid)
    {
        return $this->memcached->get($sid);
    }

    /*
     * 写入SESSION
     * @param string $sid
     * @param string $data
     * @return mixed
     */
    public function write($sid, $data)
    {
        return $this->memcached->set($sid, $data);
    }

    /*
     * 删除SESSION
     * @param string $sid SESSION_id
     * @return boolean
     */
    public function destroy($sid)
    {
        return $this->memcached->delete($sid);
    }

    /*
     * 垃圾回收
     * @return boolean
     */
    public function gc()
    {
        return true;
    }

}
