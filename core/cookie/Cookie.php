<?php namespace core\cookie;

class Cookie
{
    private $prefix = '';
    private $expire = 0;
    private $path = '/';
    private $domain = NULL;
    private $secure = FALSE;
    private $http_only = FALSE;

    function __construct()
    {
        $this->prefix = config('cookie.prefix');
        $this->expire = config('cookie.expire');
        $this->path = config('cookie.path');
        $this->domain = config('cookie.domain');
        $this->secure = config('cookie.secure');
        $this->http_only = config('cookie.http_only');
    }

    public function get($name)
    {
        if (isset($_COOKIE[$this->prefix . $name])) {
            return $_COOKIE[$this->prefix . $name];
        }else{
            return false;
        }
    }

    public function all()
    {
        return $_COOKIE;
    }

    public function set($name, $value, $expire = 0)
    {
        $expire = $expire ? NOW + $expire : $this->expire;
        setcookie($this->prefix . $name, $value, $expire, $this->path, $this->domain, $this->secure, $this->http_only);
    }

    public function del($name)
    {
        return setcookie($this->prefix . $name, '', 1);
    }

    public function has($name)
    {
        return isset($_COOKIE[$this->prefix . $name]);
    }

    public function flush()
    {
        foreach ($_COOKIE as $key => $value) {
            setcookie($key, '', 1);
        }
    }
}