<?php
return [
    //引擎:mysql,memcached,redis
    'driver' => 'file',

    //session_name
    'name' => 'session_id',

    //域名
    'domain' => '',

    //过期时间
    'expire' => 604800,

    #File       
    'file' => [
        'path' => 'data/session',
    ],

    #Mysql
    'mysql' => [
        'host' => 'localhost', 'user' => 'root', 'password' => '', 'database' => '',
    ],

    #Memcached
    'memcached' => [
        'host' => 'localhost', 'port' => 11211,
    ],

    #Redis
    'redis' => [
        'host' => 'localhost', 'port' => 11211, 'password' => '', 'database' => 0,
    ],
];