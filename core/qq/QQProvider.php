<?php namespace core\qq;

use core\kernel\ServiceProvider;

class QQProvider extends ServiceProvider
{

    //延迟加载
    public $defer = true;

    public function boot()
    {
    }

    public function register()
    {
        $this->app->single(
            'QQ',
            function () {
                return new QQ();
            }
        );
    }
}