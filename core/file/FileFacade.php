<?php namespace core\file;

use core\kernel\ServiceFacade;

class FileFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'File';
    }
}