<?php
//首页
Route::get('/', 'api/index/index');
Route::get('/count', 'api/api/count');
Route::get('/sale/(\d+)', 'api/sale/index');
Route::get('/coupon', 'api/coupon/index');
Route::before('GET', '/coupon/receive/(\d+)', ['user']);
Route::get('/coupon/receive/(\d+)', 'api/coupon/receive');
Route::before('GET', '/check', ['user']);
Route::any('/check', 'api/check/index');
Route::get('/cate/(\d+)', 'api/cate/index');
Route::get('/cate/sub/(\d+)', 'api/cate/sub');
Route::get('/cate/get/(\d+)', 'api/cate/get');
Route::get('/cate/show/(\d+)/(\d+)', 'api/cate/show');
Route::get('/search', 'api/search/index');
Route::get('/search/get', 'api/search/get');
Route::get('/pay/(\d+)', 'api/pay/index');
Route::post('/pay/notify', 'api/pay/notify');
Route::get('/ali-pay/(\d+)', 'api/ali-pay/index');
Route::get('/ali-pay/back', 'api/ali-pay/back');
Route::post('/ali-pay/notify', 'api/ali-pay/notify');
//文章
Route::get('/post', 'api/post/index');
Route::get('/post/post/(\d+)', 'api/post/post');
Route::get('/post/show/(\d+)', 'api/post/show');
//购物车
Route::get('/cart', 'api/cart/index');
Route::get('/cart/inc/(\d+)/(\d+)', 'api/cart/inc');
Route::get('/cart/dec/(\d+)/(\d+)', 'api/cart/dec');
Route::get('/cart/del/(\d+)', 'api/cart/del');
Route::get('/cart/status', 'api/cart/status');
Route::get('/cart/clear', 'api/cart/clear');
//订单催付消息队列
Route::get('/sms', 'api/order-sms/index');
//订单
Route::before('GET', '/order(.*)', ['user']);
Route::any('/order', 'api/order/index');
//用户
Route::any('/login', 'api/login/index');
Route::get('/login/send/(\d+)', 'api/login/send');
Route::get('/login/token', 'api/login/token');
Route::get('/login/out', 'api/login/out');
Route::any('/feedback', 'api/user/feedback');
Route::get('/user', 'api/user/index');
Route::before('GET|POST', '/user/(.*)', ['user']);
Route::group('/user', function () {
    Route::get('info', 'api/user/info');
    Route::get('sale', 'api/user/sale');
    Route::get('coupon', 'api/user/coupon');
    Route::get('score', 'api/user/score');
    Route::get('check', 'api/user/check');
    Route::get('about', 'api/user/about');
    Route::get('issue', 'api/user/issue');

    Route::get('order', 'api/user-order/index');
    Route::get('order/(\d+)', 'api/user-order/index');
    Route::get('order/status/(\d+)/(\d+)', 'api/user-order/status');
    Route::get('order/show/(\d+)', 'api/user-order/show');
    Route::get('order/deliveryShow/(\d+)', 'api/user-order/deliveryShow');

    Route::get('address', 'api/user-address/index');
    Route::any('address/create', 'api/user-address/create');
    Route::any('address/update', 'api/user-address/update');
    Route::get('address/delete', 'api/user-address/delete');
    Route::get('address/area', 'api/user-address/area');
});