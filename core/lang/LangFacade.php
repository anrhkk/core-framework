<?php namespace core\lang;

use core\kernel\ServiceFacade;

class LangFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Lang';
    }
}