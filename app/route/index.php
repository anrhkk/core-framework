<?php
//加载路由
require 'admin.php';
require 'api.php';
require 'restful.php';

Route::set404(function () {
    echo include '404.html';
});
//执行路由
Route::dispatch();