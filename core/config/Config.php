<?php namespace core\config;


//配置项处理
class Config
{

    //配置集合
    protected $items = [];

    /*
     * 添加配置
     *
     * @param $key
     * @param $name
     *
     * @return bool
     */
    public function set($key, $name)
    {
        $tmp =& $this->items;
        foreach (explode('.', $key) as $d) {
            if (!isset($tmp[$d])) {
                $tmp[$d] = [];
            }
            $tmp = &$tmp[$d];
        }

        $tmp = $name;

        return true;
    }

    /*
     * 获取配置
     *
     * @param $key
     *
     * @return array|void
     */
    public function get($key)
    {
        $tmp = $this->items;
        foreach (explode('.', $key) as $d) {
            if (isset($tmp[$d])) {
                $tmp = $tmp[$d];
            } else {
                return;
            }
        }

        return $tmp;
    }

    /*
     * 检测配置是否存在
     *
     * @param  [type]  $key [description]
     *
     * @return boolean      [description]
     */
    public function has($key)
    {
        $tmp = $this->items;
        foreach (explode('.', $key) as $d) {
            if (isset($tmp[$d])) {
                $tmp = $tmp[$d];
            } else {
                return false;
            }
        }

        return true;
    }

    /*
     * 获取所有配置项
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /*
     * 设置items也就是一次更改全部配置
     *
     * @param $items
     *
     * @return mixed
     */
    public function setItems($items)
    {
        return $this->items = $items;
    }
}