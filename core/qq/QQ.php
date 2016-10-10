<?php namespace core\qq;

use core\qq\org\QC;

class QQ
{

    private $QC;

    //构造函数
    public function __construct()
    {

        $this->QC = new QC;
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->QC, $method], $args);
    }
}