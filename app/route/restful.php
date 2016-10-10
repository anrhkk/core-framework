<?php
Route::group('/restful', function () {
    Route::options('/.*', 'restful/app/json');
    Route::get('app', 'restful/app/app');
    Route::get('home', 'restful/home/index');
    Route::post('login/index', 'restful/login/index');
    Route::get('login/token', 'restful/login/token');
    Route::get('login/captcha/(\d+)', 'restful/login/captcha');
    Route::get('list/leftCategory/(\d+)', 'restful/lists/leftCategory');
    Route::get('list/rightFilter/(\d+)', 'restful/lists/rightFilter');
    Route::get('list/rightItem/(\d+)', 'restful/lists/rightItem');
    Route::get('cart', 'restful/cart/index');
    Route::get('changeCart/(\d+)/(\d+)/(\d+)', 'restful/cart/change');
    Route::get('item/(\d+)/(\d+)', 'restful/item/index');
    Route::any('order', 'restful/order/index');
    Route::get('search', 'restful/search/search');
    Route::get('user/address/index', 'restful/user-address/index');
    Route::any('user/address/(\d+)', 'restful/user-address/address');
    Route::get('user/address/delete/(\d+)', 'restful/user-address/delete');
});