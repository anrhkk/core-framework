<?php namespace core\request;

use core\kernel\ServiceProvider;

class RequestProvider extends ServiceProvider
{
    //延迟加载
    public $defer = true;

    public function boot()
    {
    }

    public function register()
    {
        $this->app->single(
            'Request',
            function ($app) {
                return new Request($app);
            }
        );
    }
}