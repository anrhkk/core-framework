<?php namespace core\response;

use core\kernel\ServiceFacade;

class ResponseFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Response';
    }
}