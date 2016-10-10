<?php namespace core\security;

use core\kernel\ServiceFacade;

class SecurityFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Security';
    }
}