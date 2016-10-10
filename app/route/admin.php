<?php
//后台
Route::any('/sign-in', 'admin/admin/index');//登陆
Route::before('GET|POST', '/admin(.*)', ['admin', 'rbac']);
Route::group('/admin', function () {
    //主页
    Route::get('main/index', 'admin/main/index');
    Route::get('api/(\w+)', 'admin/admin/api');

    //图片上传
    Route::get('upload/index', 'admin/upload/index');
    Route::any('upload/upload', 'admin/upload/upload');//编辑器上传
    Route::get('upload/manage', 'admin/upload/manage');//编辑器图片管理

    /*产品中心*/
    //列表
    Route::any('spu/index', 'admin/spu/index');
    Route::any('spu/create', 'admin/spu/create');
    Route::any('spu/update', 'admin/spu/update');
    Route::get('spu/delete', 'admin/spu/delete');
    Route::get('spu/type', 'admin/spu/type');
    Route::get('spu/spec', 'admin/spu/spec');
    Route::get('spu/combine', 'admin/spu/combine');
    Route::get('spu/value', 'admin/spu/value');

    //品牌
    Route::any('brand/index', 'admin/brand/index');
    Route::any('brand/create', 'admin/brand/create');
    Route::any('brand/update', 'admin/brand/update');
    Route::get('brand/delete', 'admin/brand/delete');

    //分类
    Route::any('category/index', 'admin/category/index');
    Route::any('category/create', 'admin/category/create');
    Route::any('category/update', 'admin/category/update');
    Route::get('category/delete', 'admin/category/delete');

    //类型
    Route::any('type/index', 'admin/type/index');
    Route::any('type/create', 'admin/type/create');
    Route::any('type/update', 'admin/type/update');
    Route::get('type/delete', 'admin/type/delete');

    //类型属性
    Route::any('type-attr/index', 'admin/type-attr/index');
    Route::any('type-attr/create', 'admin/type-attr/create');
    Route::any('type-attr/update', 'admin/type-attr/update');
    Route::get('type-attr/delete', 'admin/type-attr/delete');

    //规格
    Route::any('spec/index', 'admin/spec/index');
    Route::any('spec/create', 'admin/spec/create');
    Route::any('spec/update', 'admin/spec/update');
    Route::get('spec/delete', 'admin/spec/delete');

    //规格值
    Route::any('spec-value/index', 'admin/spec-value/index');
    Route::any('spec-value/create', 'admin/spec-value/create');
    Route::any('spec-value/update', 'admin/spec-value/update');
    Route::get('spec-value/delete', 'admin/spec-value/delete');

    /*用户中心*/
    //列表
    Route::any('user/index', 'admin/user/index');
    Route::any('user/create', 'admin/user/create');
    Route::any('user/update', 'admin/user/update');
    Route::get('user/delete', 'admin/user/delete');

    //地址
    Route::any('user-address/index', 'admin/user-address/index');
    Route::any('user-address/show', 'admin/user-address/show');
    Route::get('user-address/delete', 'admin/user-address/delete');

    //意见反馈
    Route::any('feedback/index', 'admin/feedback/index');
    Route::any('feedback/delete', 'admin/feedback/delete');

    /*订单中心*/
    //订单
    Route::any('order/index', 'admin/order/index');
    Route::any('order/create', 'admin/order/create');
    Route::any('order/show', 'admin/order/show');
    Route::get('order/delete', 'admin/order/delete');
    Route::get('order/del', 'admin/order/del');
    Route::any('order/changeStauts', 'admin/order/changeStauts');
    Route::any('order/delivery', 'admin/order/delivery');
    Route::any('order/editDelivery', 'admin/order/editDelivery');
    Route::any('order/log', 'admin/order/log');

    //退货管理
    Route::any('order-return/index', 'admin/order-return/index');
    Route::any('order-return/log', 'admin/order-return/log');

    //退款管理
    Route::any('order-refund/index', 'admin/order-refund/index');
    Route::any('order-refund/log', 'admin/order-refund/log');

    /*配送中心*/
    //配送管理
    Route::any('delivery/index', 'admin/delivery/index');
    Route::any('delivery/create', 'admin/delivery/create');
    Route::any('delivery/update', 'admin/delivery/update');
    Route::get('delivery/delete', 'admin/delivery/delete');

    //地区管理
    Route::any('area/index', 'admin/area/index');
    Route::any('area/create', 'admin/area/create');
    Route::any('area/update', 'admin/area/update');
    Route::get('area/delete', 'admin/area/delete');
    Route::any('area/getArea', 'admin/area/getArea');

    Route::any('area-city/index', 'admin/area-city/index');
    Route::any('area-city/create', 'admin/area-city/create');
    Route::any('area-city/update', 'admin/area-city/update');
    Route::any('area-city/delete', 'admin/area-city/delete');

    Route::any('area-region/index', 'admin/area-region/index');
    Route::any('area-region/create', 'admin/area-region/create');
    Route::any('area-region/update', 'admin/area-region/update');
    Route::any('area-region/delete', 'admin/area-region/delete');

    /*促销中心*/
    //产品促销
    Route::any('promotion/index', 'admin/promotion/index');
    Route::any('promotion/create', 'admin/promotion/create');
    Route::any('promotion/update', 'admin/promotion/update');
    Route::get('promotion/delete', 'admin/promotion/delete');
    Route::get('promotion/type', 'admin/promotion/type');

    //订单促销
    Route::any('promotion-order/index', 'admin/promotion-order/index');
    Route::any('promotion-order/create', 'admin/promotion-order/create');
    Route::any('promotion-order/update', 'admin/promotion-order/update');
    Route::get('promotion-order/delete', 'admin/promotion-order/delete');
    Route::get('promotion-order/type', 'admin/promotion-order/type');

    //优惠券管理
    Route::any('coupon-category/index', 'admin/coupon-category/index');
    Route::any('coupon-category/create', 'admin/coupon-category/create');
    Route::any('coupon-category/update', 'admin/coupon-category/update');
    Route::get('coupon-category/delete', 'admin/coupon-category/delete');
    Route::get('coupon-category/make', 'admin/coupon-category/make');
    Route::get('coupon-category/detail', 'admin/coupon-category/detail');

    /*数据统计*/
    Route::any('count/index', 'admin/count/index');
    Route::any('count/user', 'admin/count/user');
    Route::any('count/order', 'admin/count/order');
    Route::any('count/transform', 'admin/count/transform');

    /*网站设置*/
    Route::any('config/index', 'admin/config/index');
    Route::any('config/create', 'admin/config/create');
    Route::any('config/update', 'admin/config/update');
    Route::get('config/delete', 'admin/config/delete');
    Route::get('config/type', 'admin/config/type');

    //广告
    Route::any('advert/index', 'admin/advert/index');
    Route::any('advert/create', 'admin/advert/create');
    Route::any('advert/update', 'admin/advert/update');
    Route::get('advert/delete', 'admin/advert/delete');

    //广告分类
    Route::any('advert-category/index', 'admin/advert-category/index');
    Route::any('advert-category/create', 'admin/advert-category/create');
    Route::any('advert-category/update', 'admin/advert-category/update');
    Route::get('advert-category/delete', 'admin/advert-category/delete');

    //文章管理
    Route::any('post/index', 'admin/post/index');
    Route::any('post/create', 'admin/post/create');
    Route::any('post/update', 'admin/post/update');
    Route::get('post/delete', 'admin/post/delete');

    //文章分类
    Route::any('post-category/index', 'admin/post-category/index');
    Route::any('post-category/create', 'admin/post-category/create');
    Route::any('post-category/update', 'admin/post-category/update');
    Route::get('post-category/delete', 'admin/post-category/delete');

    //搜索管理
    Route::any('search/index', 'admin/search/index');
    Route::any('search/update', 'admin/search/update');
    Route::get('search/delete', 'admin/search/delete');
});