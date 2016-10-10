<?php namespace core\response;

use core\kernel\ServiceProvider;

class ResponseProvider extends ServiceProvider
{

    //延迟加载
    public $defer = true;

    public function boot()
    {
    }

    public function register()
    {
        $this->app->single(
            'Response',
            function ($app) {
                return new Response($app);
            }
        );
    }
}