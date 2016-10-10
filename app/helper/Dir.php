<?php namespace helper;

class Dir
{
    //遍历目录
    public static function tree($dir)
    {
        $list = [];
        foreach (glob($dir . '/*') as $id => $v) {
            $info = pathinfo($v);
            $list [$id] ['path'] = $v;
            $list [$id] ['type'] = filetype($v);
            $list [$id] ['dirname'] = $info['dirname'];
            $list [$id] ['basename'] = $info['basename'];
            $list [$id] ['filename'] = $info['filename'];
            $list [$id] ['extension'] = isset($info['extension']) ? $info['extension'] : '';
            $list [$id] ['filemtime'] = filemtime($v);
            $list [$id] ['fileatime'] = fileatime($v);
            $list [$id] ['size'] = is_file($v) ? filesize($v) : self::size($v);
            $list [$id] ['iswrite'] = is_writeable($v);
            $list [$id] ['isread'] = is_readable($v);
        }

        return $list;
    }

    //获取目录在小
    public static function size($dir)
    {
        $s = 0;
        foreach (glob($dir . '/*') as $v) {
            $s += is_file($v) ? filesize($v) : self::size($v);
        }

        return $s;
    }

    //删除文件
    public static function delFile($file)
    {
        if (is_file($file)) {
            return unlink($file);
        }
        return true;
    }

    //删除目录

    public static function create($dir, $auth = 0755)
    {
        return mkdir($dir, $auth, true);
    }

    //创建目录

    public static function move($old, $new)
    {
        if (self::copy($old, $new)) {
            return self::del($old);
        }
    }

    //复制目录 

    public static function copy($old, $new)
    {
        is_dir($new) or mkdir($new, 0755, true);

        foreach (glob($old . '/*') as $v) {
            $to = $new . '/' . basename($v);
            is_file($v) ? copy($v, $to) : self::copy($v, $to);
        }

        return true;
    }

    //移动目录

    public static function del($dir)
    {
        foreach (glob($dir . "/*") as $v) {
            is_dir($v) ? self::del($v) : unlink($v);
        }

        return rmdir($dir);
    }
}