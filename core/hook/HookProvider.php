<?php namespace core\hook;

use core\kernel\ServiceProvider;

class HookProvider extends ServiceProvider
{

    //延迟加载
    public $defer = false;

    public function boot()
    {
    }

    public function register()
    {
        $this->app->single(
            'Hook',
            function ($app) {
                return new Hook($app);
            }
        );
    }
}