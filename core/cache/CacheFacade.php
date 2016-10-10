<?php namespace core\cache;

use core\kernel\ServiceFacade;

class CacheFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Cache';
    }
}