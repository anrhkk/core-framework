<?php namespace core\view;

use core\kernel\ServiceFacade;

class ViewFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'View';
    }
}