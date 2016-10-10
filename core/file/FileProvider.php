<?php namespace core\file;

use core\kernel\ServiceProvider;

class FileProvider extends ServiceProvider
{

    //延迟加载
    public $defer = true;

    public function boot()
    {

    }

    public function register()
    {
        $this->app->single(
            'File',
            function ($app) {
                return new File($app);
            }
        );
    }
}