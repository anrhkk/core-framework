<?php namespace core\config;

use core\kernel\ServiceFacade;

class ConfigFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Config';
    }
}