<?php namespace service\page;

use core\kernel\ServiceFacade;

class PageFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Page';
    }
}