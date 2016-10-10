<?php namespace core\mail;

use core\kernel\ServiceProvider;

class MailProvider extends ServiceProvider
{

    //延迟加载
    public $defer = true;

    public function boot()
    {
    }

    public function register()
    {
        $this->app->single('Mail', function ($app) {
            return new Mail($app);
        }, true);
    }
}