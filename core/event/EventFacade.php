<?php namespace core\event;

use core\kernel\ServiceFacade;

class EventFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Event';
    }
}