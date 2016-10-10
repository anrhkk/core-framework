<?php
return [
    'type' => 'file',          //类型:file memcached redis
    'expire' => 3600,          //有效时间
    'memcached' => [                //多个服务器设置二维数组
        'host' => '127.0.0.1',     //主机
        'port' => 11211,           //端口
    ],
    'redis' => [                //多个服务器设置二维数组
        'host' => '127.0.0.1',     //主机
        'port' => 6379,            //端口
        'password' => '',              //密码
        'database' => 0,               //数据库
    ],
];