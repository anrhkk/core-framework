<?php namespace core\lang;

class Lang
{

    private $data;

    public function __construct()
    {
        $this->data = require APP_PATH . '/lang/' . Config::get('app.lang') . '.php';
    }

    //获取语言
    public function get($lang)
    {
        return $this->data[$lang];
    }

    //更改语言包
    public function lang($lang)
    {
        $this->data = require APP_PATH . '/lang/' . $lang . '.php';
        return $this;
    }
}