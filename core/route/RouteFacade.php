<?php namespace core\route;

use core\kernel\ServiceFacade;

class RouteFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Route';
    }
}