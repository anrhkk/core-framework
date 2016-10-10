<?php namespace core\cookie;

use core\kernel\ServiceProvider;

class CookieProvider extends ServiceProvider
{

    //延迟加载
    public $defer = false;

    public function boot()
    {
    }

    public function register()
    {
        $this->app->single(
            'Cookie',
            function ($app) {
                return new Cookie($app);
            },
            true
        );
    }
}