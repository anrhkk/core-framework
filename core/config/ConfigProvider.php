<?php namespace core\config;
use core\kernel\ServiceProvider;
use helper\Arr;

class ConfigProvider extends ServiceProvider
{

    //延迟加载
    public $defer = false;

    public function boot()
    {
        foreach (glob(APP_PATH . DS . 'config/*') as $file) {
            $info = pathinfo($file);
            \Config::set($info['filename'], require $file);
        }
        $config_array = \DB::select('config', ['key', 'value'], ['status' => 1]);
        \Config::set('config', Arr::map($config_array, 'key', 'value'));
        //加载.env配置
        if (is_file('.env')) {
            $config = [];
            foreach (file('.env') as $file) {
                $data = explode('=', $file);
                $config[trim($data[0])] = trim($data[1]);
            }
            \Config::set('database.read.host', $config['DB_HOST']);
            \Config::set('database.read.user', $config['DB_USERNAME']);
            \Config::set('database.read.password', $config['DB_PASSWORD']);
            \Config::set('database.read.database', $config['DB_DATABASE']);
            \Config::set('database.read.prefix', $config['DB_PREFIX']);
            \Config::set('database.write.host', $config['DB_HOST']);
            \Config::set('database.write.user', $config['DB_USERNAME']);
            \Config::set('database.write.password', $config['DB_PASSWORD']);
            \Config::set('database.write.database', $config['DB_DATABASE']);
            \Config::set('database.write.prefix', $config['DB_PREFIX']);
        }
    }

    public function register()
    {
        $this->app->single(
            'Config',
            function ($app) {
                return new Config($app);
            },
            true
        );
    }
}