<?php namespace helper;

class Data
{
    /**
     * 用于树型数组完成递归格式的全局变量
     */
    private static $tree;

    /**
     * 将格式数组转换为基于标题的树（实际还是列表，只是通过在相应字段加前缀实现类似树状结构）
     * @param array $list
     * @param integer $level 进行递归时传递用的参数
     */
    private static function _tree($list, $level = 0, $name = 'name')
    {
        foreach ($list as $key => $val) {
            $name_prefix = str_repeat("&nbsp;", $level * 4);
            $name_prefix .= "┝ ";
            $val['level'] = $level;
            $val['name'] = $level == 0 ? $val[$name] : $name_prefix . $val[$name];
            if (!array_key_exists('_child', $val)) {
                array_push(self::$tree, $val);
            } else {
                $child = $val['_child'];
                unset($val['_child']);
                array_push(self::$tree, $val);
                self::_tree($child, $level + 1, $name); //进行下一层递归
            }
        }
        return;
    }

    /**
     * 将格式数组转换为树
     * @param array $list
     * @param integer $level 进行递归时传递用的参数
     */
    public static function tree($list, $name = 'name', $pk = 'id', $pid = 'parent_id', $root = 0)
    {
        $list = self::list_to_tree($list, $pk, $pid, '_child', $root);
        self::$tree = [];
        self::_tree($list, 0, $name);
        return self::$tree;
    }

    /**
     * 将数据集转换成Tree（真正的Tree结构）
     * @param array $list 要转换的数据集
     * @param string $pid parent标记字段
     * @param string $level level标记字段
     * @return array
     */
    public static function list_to_tree($list, $pk = 'id', $pid = 'parent_id', $child = '_child', $root = 0)
    {
        // 创建Tree
        $tree = [];
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = [];
            foreach ($list as $key => $val) {
                $refer[$val[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $val) {
                // 判断是否存在parent
                $parent_id = $val[$pid];
                if ($parent_id === null || (String)$root === $parent_id) {
                    $tree[] =& $list[$key];
                } else {
                    if (isset($refer[$parent_id])) {
                        $parent =& $refer[$parent_id];
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }

    /**
     * 将list_tree的树还原成列表
     * @param    array $tree 原来的树
     * @param    string $child 孩子节点的键
     * @param    string $order 排序显示的键，一般是主键 升序排列
     * @param    array $list 过渡用的中间数组，
     * @return array 返回排过序的列表数组
     */
    public static function tree_to_list($tree, $child = '_child', $order = 'id', &$list = array())
    {
        if (is_array($tree)) {
            foreach ($tree as $key => $val) {
                $refer = $val;
                if (isset($refer[$child])) {
                    unset($refer[$child]);
                    self::tree_to_list($val[$child], $child, $order, $list);
                }
                $list[] = $refer;
            }
            $list = self::list_sort_by($list, $order, $sort = 'asc');
        }
        return $list;
    }

    /**
     * 对查询结果集进行排序
     * @access public
     * @param array $list 查询结果
     * @param string $field 排序的字段名
     * @param array $sort 排序类型 asc正向排序 desc逆向排序 nat自然排序
     * @return array
     */
    public static function list_sort_by($list, $field, $sort = 'asc')
    {
        if (is_array($list)) {
            $refer = $resultSet = [];
            foreach ($list as $key => $val)
                $refer[$key] = &$val[$field];
            switch ($sort) {
                case 'asc': // 正向排序
                    asort($refer);
                    break;
                case 'desc':// 逆向排序
                    arsort($refer);
                    break;
                case 'nat': // 自然排序
                    natcasesort($refer);
                    break;
            }
            foreach ($refer as $key => $val)
                $resultSet[] = &$list[$key];
            return $resultSet;
        }
        return false;
    }

    /**
     * 在数据列表中搜索
     * @access public
     * @param array $list 数据列表
     * @param mixed $condition 查询条件
     * 支持 array('name'=>$value) 或者 name=$value
     * @return array
     */
    public static function list_search($list, $condition)
    {
        if (is_string($condition))
            parse_str($condition, $condition);
        //返回的结果集合
        $resultSet = [];
        foreach ($list as $key => $val) {
            $find = false;
            foreach ($condition as $field => $value) {
                if (isset($val[$field])) {
                    if (0 === strpos($value, '/')) {
                        $find = preg_match($value, $val[$field]);
                    } elseif ($val[$field] == $value) {
                        $find = true;
                    }
                }
            }
            if ($find) {
                $resultSet[] = &$list[$key];
            }
        }
        return $resultSet;
    }
}