<?php namespace core\session;

use core\kernel\ServiceFacade;

class SessionFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Session';
    }
}