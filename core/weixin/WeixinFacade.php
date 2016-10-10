<?php namespace core\weixin;

use core\kernel\ServiceFacade;

class WeixinFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Weixin';
    }
}