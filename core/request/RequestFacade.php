<?php namespace core\request;

use core\kernel\ServiceFacade;

class RequestFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Request';
    }
}