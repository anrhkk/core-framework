<?php namespace core\session;

//Session处理类
class Session
{
    public function __construct()
    {
        $this->init();
        $driver = '\core\session\\' . ucfirst(Config::get('session.driver'));
        $this->driver = new $driver();
    }

    //session初始
    private function init()
    {
        session_name(Config::get('session.name'));

        //post存在session时使用post做为sessionid值
        if ($session_id = Request::requrest(session_name())) {
            session_id($session_id);
        }

        if ($domain = Config::get('session.domain')) {
            ini_set('session.cookie_domain', $domain);
        }

    }

    public function has($name)
    {
        return isset($_SESSION[$name]);
    }

    public function set($name, $value)
    {
        $tmp =& $_SESSION;
        foreach (explode('.', $name) as $d) {
            if (!isset($tmp[$d])) {
                $tmp[$d] = [];
            }
            $tmp = &$tmp[$d];
        }

        return $tmp = $value;
    }

    public function get($name = '')
    {
        $tmp = $_SESSION;
        foreach (explode('.', $name) as $d) {
            if (isset($tmp[$d])) {
                $tmp = $tmp[$d];
            } else {
                return;
            }
        }

        return $tmp;
    }

    public function del($name)
    {
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }

        return true;
    }

    public function all()
    {
        return $_SESSION;
    }

    public function flush()
    {
        session_unset();
        session_destroy();
    }

    public function __call($method, $params)
    {
        return call_user_func_array([new $this->driver, $method], $params);
    }
}