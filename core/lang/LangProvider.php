<?php namespace core\lang;

use core\kernel\ServiceProvider;

class LangProvider extends ServiceProvider
{

    //延迟加载
    public $defer = true;

    public function boot()
    {

    }

    public function register()
    {
        $this->app->single(
            'Lang',
            function ($app) {
                return new Lang($app);
            }
        );
    }
}