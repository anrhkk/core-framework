<?php namespace core\qq;

use core\kernel\ServiceFacade;

class QQFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'QQ';
    }
}