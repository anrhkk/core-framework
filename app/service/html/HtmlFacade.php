<?php namespace service\html;

use core\kernel\ServiceFacade;

class HtmlFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Html';
    }
}