<?php namespace service\html;

class Html
{
    /*
     * 下拉框
     * @param $title 标题
     * @param $name name
     * @param $options 值
     * @param string $selected
     */
    public function select($title, $name, $selected = '', array $options = [], $multiple = '')
    {
        $options = $options ? $options : [1 => lang('Yes'), 0 => lang('No')];
        $option = '<option value="">' . $title . '</option>';
        foreach ($options as $key => $val) {
            $sed = $selected == $key && $selected != '' ? ' selected="selected" ' : '';
            $option .= <<<str
                <option value="{$key}" {$sed}>$val</option>
str;
        }
        $html = <<<str
        <div class="field">
                <select id="{$name}" name="{$name}" class="ui dropdown" {$multiple}>
                    {$option}
                </select>
            </div>
str;
        return $html;
    }

    public function image($src)
    {
        $src = $src ? $src : config('app.logo');
        $html = <<<str
        <img src="{$src}" width="60px">
str;
        return $html;
    }

    /*
     * ajax修改
     */
    public function modify($id, $name, $value)
    {
        $html = <<<str
        <a href="javascript:;" class="modify" data-id="{$id}" data-name="{$name}" data-value="{$value}">{$value}</a>
str;
        return $html;
    }

    /*
     * 复选框ajax选择
     */
    public function yes_no($id, $name, $checked)
    {
        $checked = $checked == 1 ? 'checked="checked"' : '';
        $html = <<<str
        <div class="ui toggle checkbox">
            <input type="checkbox" tabindex="0" class="hidden" id="{$id}" name="{$name}" $checked>
        </div>
str;
        return $html;
    }

    /*
     * 下拉框ajax选择
     */
    public function change($id, $name, $selected = -1, array $options = [] , $class = '')
    {
        $options = $options ? $options : [1 => lang('Yes'), 0 => lang('No')];
        $option = '';
        foreach ($options as $value => $t) {
            $sed = $selected == $value ? ' selected="selected" ' : '';
            $option .= <<<str
                <option value="{$value}" {$sed}>$t</option>
str;
        }
        $html = <<<str
        <select id="{$id}" name="{$name}" class="ui dropdown change {$class}">
             {$option}
        </select>
str;
        return $html;
    }

    //时间格式化
    public function date_format($time)
    {
        return !empty($time) ? date('Y-m-d H:i', $time) : '暂无';
    }

    public function sort($title, $field, $sort = 'desc')
    {
        $sort = $sort == 'desc' ? 'asc' : 'desc';
        $direction = $sort == 'desc' ? 'down' : 'up';
        $url = parse_url(__URL__);
        parse_str($url['query'], $param);
        $param['sort'] = $field . '-' . $sort;
        $param['page'] = 1;
        $url = urldecode($url['path'] . '?' . http_build_query($param));
        $html = <<<str
        <a href="{$url}">{$title}<i class="chevron {$direction} icon"></i></a>
str;
        return $html;
    }

    //复选框ajax选择
    public function int2str($id, array $array = [])
    {
        $array = $array ? $array : [1 => lang('Yes'), 0 => lang('No')];
        if ($array[$id]) return $array[$id];
    }
}