<?php namespace service\form;

use core\kernel\ServiceProvider;

class FormProvider extends ServiceProvider
{

    //延迟加载
    public $defer = true;

    public function boot()
    {
    }

    public function register()
    {
        $this->app->single('Form', function ($app) {
            return new Form($app);
        });
    }
}