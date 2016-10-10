<?php namespace core\security;

use core\kernel\ServiceProvider;

class SecurityProvider extends ServiceProvider
{

    //延迟加载
    public $defer = true;

    public function register()
    {
        $this->app->single(
            'Security',
            function ($app) {
                return new Security($app);
            }
        );
    }
}