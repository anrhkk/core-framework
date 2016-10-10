<?php namespace core\rbac;

use core\kernel\ServiceFacade;

class RbacFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Rbac';
    }
}