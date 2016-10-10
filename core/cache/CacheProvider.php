<?php namespace core\cache;

use core\kernel\ServiceProvider;

class CacheProvider extends ServiceProvider
{

    //延迟加载
    public $defer = true;

    public function boot()
    {
    }

    public function register()
    {
        $this->app->single('Cache', function ($app) {
            return new Cache($app);
        }
        );
    }
}