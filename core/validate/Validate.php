<?php namespace core\validate;

use Closure;

class Validate
{
    //有字段时验证
    const EXISTS_VALIDATE = 1;
    //值不为空时验证
    const VALUE_VALIDATE = 2;
    //必须验证
    const MUST_VALIDATE = 3;
    //插入时处理
    const INSERT_VALIDATE = 1;
    //更新时处理
    const UPDATE_VALIDATE = 2;
    //闭包函数
    public static $validate = [];
    //错误信息
    public $message = '';

    /*
     * 表单验证
     * @param $validates 验证规则
     * @param array $data 数据
     * @return $this
     */
    public function make($validates, array $data = [], $type = 3)
    {
        $data = $data ? $data : Request::post('.');

        foreach ($validates as $validate) {
            //验证条件
            $validate[3] = isset($validate[3]) ? $validate[3] : self::EXISTS_VALIDATE;
            //有这个字段验证
            if ($validate[3] == self::EXISTS_VALIDATE && !isset($data[$validate[0]])) {
                continue;
            } else if ($validate[3] == self::VALUE_VALIDATE && empty($data[$validate[0]])) {
                //不为空时验证
                continue;
            } else if ($validate[3] == self::MUST_VALIDATE) {
                //必须验证
            }
            $validate[4] = isset($validate[4]) ? $validate[4] : self::MUST_VALIDATE;
            //验证时间判断
            if ($validate[4] != $type && $validate[4] != self::MUST_VALIDATE) {
                continue;
            }
            //字段名
            $field = $validate[0];
            //验证规则
            $actions = explode('|', $validate[1]);
            //错误信息
            $message = $validate[2];
            //表单值
            $value = isset($data[$field]) ? $data[$field] : '';
            foreach ($actions as $action) {
                $info = explode(':', $action);
                $method = $info[0];
                $params = isset($info[1]) ? $info[1] : '';

                if (method_exists($this, $method)) {
                    //类方法验证
                    if ($this->$method($field, $value, $params) !== true) {
                        $_SESSION['_validate'] = $this->message = $message;
                        return $this;
                    }
                } else if (isset(self::$validate[$method])) {
                    $callback = self::$validate[$method];
                    if ($callback instanceof Closure) {
                        //闭包函数
                        if ($callback($field, $value, $params) !== true) {
                            $_SESSION['_validate'] = $this->message = $message;
                            return $this;
                        }
                    }
                }
            }
        }

        //令牌验证
        if (config('security.csrf_protection')) {
            if (Security::csrfVerify() === FALSE) {
                $this->message = '表单令牌错误';
                return $this;
            }
        }

        $_SESSION['_validate'] = $this->message = '';
        return $this;
    }

    //添加验证闭包
    public function extend($name, $callback)
    {
        if ($callback instanceof Closure) {
            self::$validate[$name] = $callback;
        }
    }

    //验证失败检测
    public function fail()
    {
        return !empty($this->message);
    }

    //获取错误信息
    public function message()
    {
        return $this->message;
    }

    /*
     * 内容不能为空
     * @param $name
     * @param $value
     * @param $msg
     * @return bool
     */
    private function required($name, $value, $params)
    {
        if (!empty($value)) {
            return true;
        }
    }

    /*
     * 邮箱验证
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     * @return bool
     */
    private function email($name, $value, $params)
    {
        $preg = "/^([a-zA-Z0-9_\-\.])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/i";
        if (preg_match($preg, $value)) {
            return true;
        }
    }

    /*
     * 最大长度验证
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     * @return bool
     */
    private function max($name, $value, $params)
    {
        if (mb_strlen($value, 'utf-8') <= $params) {
            return true;
        }
    }

    /*
     * 最小长度验证
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     * @return bool
     */
    private function min($name, $value, $params)
    {
        if (mb_strlen($value, 'utf-8') >= $params) {
            return true;
        }
    }

    /*
     * 网址验证
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     * @return bool
     */
    private function http($name, $value, $params)
    {
        $preg = "/^(http[s]?:)?(\/{2})?([a-z0-9]+\.)?[a-z0-9]+(\.(com|cn|cc|org|net|com.cn))$/i";
        if (preg_match($preg, $value)) {
            return true;
        }
    }

    /*
     * 电话号码
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     * @return bool
     */
    private function tel($name, $value, $params)
    {
        $preg = "/(?:\(\d{3,4}\)|\d{3,4}-?)\d{8}/";
        if (preg_match($preg, $value)) {
            return true;
        }
    }

    /*
     * 手机号验证
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     * @return bool
     */
    private function phone($name, $value, $params)
    {
        $preg = "/^\d{11}$/";
        if (preg_match($preg, $value)) {
            return true;
        }
    }

    /*
     * 身份证验证
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     * @return bool
     */
    private function identity($name, $value, $params)
    {
        $preg = "/^(\d{15}|\d{18})$/";
        if (preg_match($preg, $value)) {
            return true;
        }
    }

    /*
     * 用户名验证
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     * @return bool
     */
    private function user($name, $value, $params)
    {
        //用户名长度
        $len = mb_strlen($value, 'utf-8');
        $params = explode(',', $params);
        if ($len >= $params[0] && $len <= $params[1]) {
            return true;
        }
    }

    /*
     * 数字验证
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     * @return bool
     */
    private function number($name, $value, $params)
    {
        $preg = '/^\d+$/';
        if (preg_match($preg, $value)) {
            return true;
        }
    }

    /*
     * 数字范围
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     * @return bool
     */
    private function num($name, $value, $params)
    {
        $params = explode(',', $params);
        if ($value >= $params[0] && $value <= $params[1]) {
            return true;
        }
    }

    /*
     * 正则验证
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     * @return bool
     */
    private function regexp($name, $value, $preg)
    {
        if (preg_match($preg, $value)) {
            return true;
        }
    }

    /*
     * 两个表单比对
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     * @return bool
     */
    private function confirm($name, $value, $params)
    {
        if ($value == $_POST[$params]) {
            return true;
        }
    }

    /*
     * 数值对比
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     * @return bool
     */
    private function equal($name, $value, $params)
    {
        if ($value == $params) {
            return true;
        }
    }

    /*
     * 中文验证
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     * @return bool
     */
    private function china($name, $value, $params)
    {
        if (preg_match('/^[\x{4e00}-\x{9fa5}a-z0-9]+$/ui', $value)) {
            return true;
        }
    }

    private function unique($field, $value, $param)
    {
        if (!DB::get($param, $field, [$field => $value])) {
            return true;
        }
    }

}