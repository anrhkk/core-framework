<?php
if (version_compare(PHP_VERSION, '5.4.0', '<')) die('require PHP > 5.4.0 !');

//开启调试模式
define('DEBUG', true);

//应用目录
define('APP_PATH', '../app');

//自动加载
require __DIR__.'/../vendor/autoload.php';