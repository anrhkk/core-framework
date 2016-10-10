<?php namespace core\mail;

use core\kernel\ServiceFacade;

class MailFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Mail';
    }
}