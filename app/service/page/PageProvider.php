<?php namespace service\page;

use core\kernel\ServiceProvider;

class PageProvider extends ServiceProvider
{

    //延迟加载
    public $defer = true;

    public function boot()
    {
    }

    public function register()
    {
        $this->app->single('Page', function ($app) {
            return new Page($app);
        });
    }
}