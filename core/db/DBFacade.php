<?php namespace core\db;

use core\kernel\ServiceFacade;

class DBFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'DB';
    }
}