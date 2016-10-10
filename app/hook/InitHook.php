<?php
namespace hook;
class InitHook
{
    public function run(&$test)
    {
        $test = $test + 2;
    }
}