<?php namespace core\weixin;

use core\kernel\ServiceProvider;

class WeixinProvider extends ServiceProvider
{

    //延迟加载
    public $defer = true;

    public function boot()
    {
    }

    public function register()
    {
        $this->app->single(
            'Weixin',
            function () {
                return new Weixin();
            }
        );
    }
}