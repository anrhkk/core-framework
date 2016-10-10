<?php namespace core\error;

use core\kernel\ServiceFacade;

class ErrorFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Error';
    }
}