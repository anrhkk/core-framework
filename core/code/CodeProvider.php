<?php namespace core\code;

use core\kernel\ServiceProvider;

class CodeProvider extends ServiceProvider
{

    //延迟加载
    public $defer = false;

    public function boot()
    {
    }

    public function register()
    {
        $this->app->single(
            'Code',
            function ($app) {
                return new Code($app);
            },
            true
        );
    }
}