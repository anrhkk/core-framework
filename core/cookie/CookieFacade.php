<?php namespace core\cookie;

use core\kernel\ServiceFacade;

class CookieFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Cookie';
    }
}