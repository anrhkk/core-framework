<?php namespace core\session;

class File implements SessionInterface
{
    //执行SESSION控制
    public function make()
    {
        //创建目录
        if (!is_dir(ROOT_PATH . '/data/session')) {
            mkdir(ROOT_PATH . '/data/session', 0755, true);
        }

        //设置session保存目录
        session_save_path(ROOT_PATH . '/data/session');
        //开启session
        session_start();
    }
}