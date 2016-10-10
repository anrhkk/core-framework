<?php namespace core\alipay;

use core\kernel\ServiceFacade;

class AlipayFacade extends ServiceFacade
{
    public static function getFacadeAccessor()
    {
        return 'Alipay';
    }
}