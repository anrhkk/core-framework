<?php namespace service\form;

use core\kernel\ServiceFacade;

class FormFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Form';
    }
}