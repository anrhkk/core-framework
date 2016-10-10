<?php namespace service\form;

class Form
{
    protected $validate = [];//验证规则

    //构造函数
    function __construct()
    {
        $this->validate = [
            ['name', 'required', lang('NameRequired')],
        ];
    }

    public function modify($table)
    {
        $id = request('post', 'id', 0, 'intval');
        $name = request('post', 'name');
        $value = request('post', 'value', 0, 'intval');
        $result = DB::update($table, [$name => $value], ['id' => $id]);
        if ($result) {
            response(lang('ModifySuccess'), 1);
        } else {
            response(lang('ModifyFail'), 1);
        }
    }

    public function insert($table, array $data = [], array $validate = [], $ajax = true)
    {
        $validate = $validate ? $validate : $this->validate;
        if (Validate::make($validate)->fail()) {
            response(Validate::message(), 0);
        } else {
            $data = $data ? $data : request('post', '.');
            $data = $this->field($table, $data);
            $data['created_at'] = NOW;
            $data['updated_at'] = NOW;
            $id = DB::insert($table, $data);
            if ($ajax) {
                if ($id) {
                    response(lang('CreateSuccess'), 1);
                } else {
                    response(lang('CreateFail'), 0);
                }
            } else {
                return $id;
            }

        }
    }

    public function field($table, $data)
    {
        $table_fields = DB::getFields($table);
        $data_fields = array_keys($data);
        foreach ($data_fields as $field) {
            if (!in_array($field, $table_fields)) {
                unset($data[$field]);
            }
        }
        return $data;
    }

    public function update($table, array $data = [], array $where = [], array $validate = [])
    {
        $validate = $validate ? $validate : $this->validate;
        if (Validate::make($validate)->fail()) {
            response(Validate::message(), 0);
        } else {
            $data = $data ? $data : request('post', '.');
            $data = $this->field($table, $data);
            $where = $where ? $where : ['id' => request('get', 'id', 0, 'intval')];
            $data['updated_at'] = NOW;
            $result = DB::update($table, $data, $where);
            if ($result) {
                response(lang('UpdateSuccess'), 1);
            } else {
                response(lang('UpdateFail'), 0);
            }
        }
    }

    public function delete($table, array $where = [])
    {
        $where = $where ? $where : ['id' => request('get', 'id', 0, 'intval')];
        $result = DB::delete($table, $where);
        if ($result) {
            response(lang('DeleteSuccess'), 1);
        } else {
            response(lang('DeleteFail'), 0);
        }
    }

    /*
     * 下拉框
     * @param $title 标题
     * @param $name name
     * @param $options 值
     * @param string $selected
     */

    public function select($title, $name, $selected = 0, $options = [], $id = '', $multiple = '', $class = '')
    {
        $options = $options ? $options : ($multiple ? [] : [1 => lang('Yes'), 0 => lang('No')]);
        $option = $multiple ? '' : '<option value="">' . lang('Choose') . '</option>';
        foreach ($options as $value => $t) {
            $sed = (strpos($selected, ',') !== FALSE && in_array($value, str2arr($selected))) || $selected == $value ? ' selected="selected" ' : '';
            $option .= <<<str
                <option value="{$value}" {$sed}>$t</option>
str;
        }
        $id = $id ? $id : $name;
        $html = <<<str
        <div class="inline fields">
            <label for="{$name}">$title</label>
            <div class="field">
                <select id="{$id}" name="{$name}" class="ui search selection dropdown {$class}" {$multiple}>
                    {$option}
                </select>
            </div>
        </div>
str;
        return $html;
    }

    /*
     * 隐藏域
     */
    public function hidden($name, $value = '', $id = '', $class = '')
    {
        $html = <<<str
       <input type="hidden" class="{$class}" name="{$name}" id="{$id}" value="{$value}">
str;
        return $html;
    }

    /*
     * 时间选择
     */
    public function datetime($name, $value = '', $id = '', $class = '')
    {
        $value = Html::date_format($value);
        $html = <<<str
       <input type="text" class="{$class}" name="{$name}" id="{$id}" value="{$value}" onclick="datetime({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})">
str;
        return $html;
    }

    /*
     * 文本框
     * @param $title 标题
     * @param $name name
     * @param string $value 值
     * @param array $attr 选项
     */
    public function input($title, $name, $value = '', $attr = [], $tip = '')
    {
        $a = '';
        if (!isset($attr['type'])) {
            $attr['type'] = 'text';
        }
        foreach ($attr as $n => $v) {
            $a .= $n . '="' . $value . '" ';
        }
        $html = <<<str
        <div class="form-group">
                    <label class="col-lg-2 control-label">{$title}</label>
                    <div class="col-lg-10">
                        <input type="text" name="{$name}" value="{$value}" $a class="form-control">
                        <span class="help-block m-b-none">$tip</span>
                    </div>
        </div>
str;
        return $html;
    }

    /*
     * 单选框
     * @param $title 标题
     * @param $name name
     * @param $options 值
     * @param string $selected
     */
    public function radio($title, $name, $checked = 0, $options = '')
    {
        $options = $options ? $options : [1 => lang('Yes'), 0 => lang('No')];
        if (!is_array($options)) {
            $value = [];
            foreach (explode(',', $options) as $val) {
                $value[$val] = $val;
            }
            $options = $value;
        }
        $radio = '';
        foreach ($options as $value => $t) {
            $sed = $checked == $value ? ' checked="checked" ' : '';
            $radio .= <<<str
            <div class="ui radio checkbox">
                <input type="radio" id="{$name}" class="hidden" name="{$name}" value="{$value}" tabindex="{$value}" {$sed}>
                <label>$t</label>
            </div>
str;
        }
        $label = $title ? '<label for="' . $name . '">' . $title . '</label>' : '';
        $html = <<<str
       <div class="inline fields">
                    {$label}
                    <div class="field">
                        {$radio}
                    </div>
                </div>
str;
        return $html;
    }

    /*
     * 复选框
     * @param $title 标题
     * @param $name name
     * @param $options 值
     * @param string $selected
     */
    public function checkbox($title, $name, $checked = '', $options = '')
    {
        if (!empty($options) && !is_array($options)) {
            $value = [];
            foreach (explode(',', $options) as $val) {
                $value[$val] = $val;
            }
            $options = $value;
        }
        $checkbox = '';
        foreach ($options as $value => $t) {
            $sed = ((strpos($checked, '.') !== FALSE && in_array($value, explode('.', $checked))) || (strpos($checked, ',') !== FALSE && in_array($value, str2arr($checked)))) || $checked == $value ? ' checked="checked" ' : '';
            $checkbox .= <<<str
            <div class="ui checkbox">
                <input type="checkbox" class="hidden" name="{$name}" value="{$value}" tabindex="{$value}" {$sed}>
                <label>$t</label>
            </div>
str;
        }
        $label = $title ? '<label for="' . $name . '">' . $title . '</label>' : '';
        $html = <<<str
       <div class="inline fields">
                    {$label}
                    <div class="field">
                        {$checkbox}
                    </div>
                </div>
str;
        return $html;
    }

    /*
     * 上传
     */
    public function upload($name, $src = '', $id = '', $url = '')
    {
        $url = $url ? $url : url('upload/index');
        $html = <<<str
        <button type="button" class="ui icon button upload-image" id="{$id}" data-url="{$url}"><i class="upload icon"></i></button>
        <input type="hidden" name="{$name}" value="{$src}"/>
str;
        return $html;
    }

    /*
     * 提交按钮
     */
    public function submit()
    {
        $html = <<<str
        <div class="ui button submit green">{{lang('Submit')}}</div>
        <div class="ui reset button">{{lang('Reset')}}</div>
        <div class="ui button" onclick="location.href=window.history.back()">{{lang('Back')}}</div>
str;
        return $html;
    }
}