<?php namespace core\log;

use core\kernel\ServiceFacade;

class LogFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Log';
    }
}