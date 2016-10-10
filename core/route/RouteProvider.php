<?php namespace core\route;

use core\kernel\ServiceProvider;

class RouteProvider extends ServiceProvider
{

    //延迟加载
    public $defer = false;

    public function boot()
    {

    }

    public function register()
    {
        $this->app->single(
            'Route',
            function ($app) {
                return new Route($app);
            },
            true
        );
    }
}