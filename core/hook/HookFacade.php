<?php namespace core\hook;

use core\kernel\ServiceFacade;

class HookFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Hook';
    }
}