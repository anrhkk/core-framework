<?php namespace core\db;

use core\kernel\ServiceProvider;

class DBProvider extends ServiceProvider
{

    //延迟加载
    public $defer = false;

    public function boot()
    {

    }

    public function register()
    {
        $this->app->bind(
            'DB',
            function ($app) {
                return new DB($app);
            },
            true
        );
    }
}