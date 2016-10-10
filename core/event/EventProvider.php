<?php namespace core\event;

use core\kernel\ServiceProvider;

class EventProvider extends ServiceProvider
{
    //延迟加载
    public $defer = false;

    public function boot()
    {

    }

    public function register()
    {
        $this->app->single(
            'Event',
            function ($app) {
                return new Event($app);
            },
            true
        );
    }
}