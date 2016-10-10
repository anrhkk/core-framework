<?php namespace helper;

//数组处理类
class Arr
{
    /**
     * Builds a map (key-value pairs) from a multidimensional array or an array of objects.
     * The `$from` and `$to` parameters specify the key names or property names to set up the map.
     * Optionally, one can further group the map according to a grouping field `$group`.
     *
     * For example,
     *
     * ```php
     * $array = [
     *     ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
     *     ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
     *     ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
     * ];
     *
     * $result = ArrayHelper::map($array, 'id', 'name');
     * // the result is:
     * // [
     * //     '123' => 'aaa',
     * //     '124' => 'bbb',
     * //     '345' => 'ccc',
     * // ]
     *
     * $result = ArrayHelper::map($array, 'id', 'name', 'class');
     * // the result is:
     * // [
     * //     'x' => [
     * //         '123' => 'aaa',
     * //         '124' => 'bbb',
     * //     ],
     * //     'y' => [
     * //         '345' => 'ccc',
     * //     ],
     * // ]
     * ```
     *
     * @param array $array
     * @param string|\Closure $from
     * @param string|\Closure $to
     * @param string|\Closure $group
     * @return array
     */
    public static function map($array, $from, $to, $group = null)
    {
        $result = [];
        foreach ($array as $element) {
            $key = static::getValue($element, $from);
            $value = static::getValue($element, $to);
            if ($group !== null) {
                $result[static::getValue($element, $group)][$key] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public static function getValue($array, $key, $default = null)
    {
        if ($key instanceof \Closure) {
            return $key($array, $default);
        }

        if (is_array($key)) {
            $lastKey = array_pop($key);
            foreach ($key as $keyPart) {
                $array = static::getValue($array, $keyPart);
            }
            $key = $lastKey;
        }

        if (is_array($array) && array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (($pos = strrpos($key, '.')) !== false) {
            $array = static::getValue($array, substr($key, 0, $pos), $default);
            $key = substr($key, $pos + 1);
        }

        if (is_object($array)) {
            // this is expected to fail if the property does not exist, or __get() is not implemented
            // it is not reliably possible to check whether a property is accessable beforehand
            return $array->$key;
        } elseif (is_array($array)) {
            return array_key_exists($key, $array) ? $array[$key] : $default;
        } else {
            return $default;
        }
    }

    /*
     * 不区分大小写检测数据键名是否存在
     * @param  [type] $key [键名]
     * @param  [type] $arr [数组]
     * @return [bool]      [description]
     */
    public static function array_key_exists($key, $arr)
    {
        return array_key_exists(strtolower($key), self::array_change_key_case($arr));
    }

    /*
     * 将数组键名变成大写或小写
     * @param array $arr 数组
     * @param int $type 转换方式 1大写   0小写
     * @return array
     */
    public static function array_change_key_case($arr, $type = 0)
    {
        $func = $type ? 'strtoupper' : 'strtolower';
        $data = []; //格式化后的数组
        foreach ($arr as $k => $v) {
            $k = $func($k);
            $data[$k] = is_array($v) ? self::array_change_key_case($v, $type) : $v;
        }
        return $data;
    }

    /*
     * 将数组中的值全部转为大写或小写
     * @param array $arr
     * @param int $type 类型 1值大写 0值小写
     * @return array
     */
    public static function array_change_value_case($arr, $type = 0)
    {
        $func = $type ? 'strtoupper' : 'strtolower';
        $data = []; //格式化后的数组
        foreach ($arr as $k => $v) {
            $data[$k] = is_array($v) ? self::array_change_value_case($v, $type) : $func($v);
        }

        return $data;
    }

    /*
     * 数组进行整数映射转换
     * @param       $data
     * @param array $map
     */
    public static function int_to_string(&$arr, array $map = ['status' => ['0' => '禁止', '1' => '启用']])
    {
        foreach ($map as $name => $m) {
            if (isset($arr[$name]) && array_key_exists($arr[$name], $m)) {
                $arr['_' . $name] = $m[$arr[$name]];
            }
        }
        return $arr;
    }
}