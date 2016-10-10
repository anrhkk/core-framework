<?php namespace core\code;

use core\kernel\ServiceFacade;

class CodeFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Code';
    }
}