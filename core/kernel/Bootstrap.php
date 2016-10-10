<?php namespace core\kernel;

class Bootstrap
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        //时区
        date_default_timezone_set(config('app.timezone'));

        //导入钓子
        $this->app['Hook']->import(config('hook'));

        //保存日志
        $this->app['Log']->save();

        //加载路由
        require APP_PATH . '/route/index.php';
    }
}