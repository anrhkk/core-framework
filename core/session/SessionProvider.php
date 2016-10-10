<?php namespace core\session;

use core\kernel\ServiceProvider;

class SessionProvider extends ServiceProvider
{

    //延迟加载
    public $defer = false;

    public function boot()
    {
        \Session::make();

        //设置过期时间,即设置cookie的PHPSESSID
        $expire = Config::get('session.expire');
        if ($expire > 0) {
            setcookie(session_name(), session_id(), NOW + $expire, '/');
        }
    }

    public function register()
    {
        $this->app->single(
            'Session',
            function ($app) {
                return new Session($app);
            }
        );
    }
}