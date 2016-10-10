<?php namespace core\validate;

use core\kernel\ServiceFacade;

class ValidateFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Validate';
    }
}