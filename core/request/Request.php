<?php namespace core\request;

class Request
{

    public function __call($method, $params)
    {
        $params[0] = $method . ':' . $params[0];
        return call_user_func_array([$this, 'query'], $params);
    }

    /*
     * 获取变量
     *
     * @param       $var     变量名
     * @param null $default 变量不存在时设置的值
     * @param array $filter 过滤函数
     *
     * @return array|null
     */
    public function query($var, $default = null, $filter = ['htmlspecialchars'])
    {
        //$var=get:id 或 get:.
        $var = explode(":", $var);
        switch (strtoupper($var[0])) {
            case 'GET' :
                $data = &$_GET;
                break;
            case 'POST' :
                $data = &$_POST;
                break;
            case 'REQUEST' :
                $data = &$_REQUEST;
                break;
            case 'FILES' :
                $data = &$_FILES;
                break;
            case 'SESSION' :
                $data = &$_SESSION;
                break;
            case 'COOKIE' :
                $data = &$_COOKIE;
                break;
            case 'SERVER' :
                $data = &$_SERVER;
                break;
            case 'GLOBALS' :
                $data = &$GLOBALS;
                break;
            default :
                return;
        }
        //Request::post(".")返回所有
        if ($var[1] == '.') {
            return $this->filter($data, $filter);
        } else if (isset($data[$var[1]])) {
            return $this->filter($data[$var[1]], $filter);
        } else {
            return $data[$var[1]] = $default;
        }
    }

    public function filter($data, $filter)
    {
        if (is_array($data)) {
            while (list($key) = each($data)) {
                $data[$key] = $this->filter($data[$key], $filter);
            }
            return $data;
        }
        if (!empty($filter)) {
            if (is_string($filter)) {
                $filter = explode(',', $filter);
            }
            //过滤处理
            for ($i = 0; $i < count($filter); $i++) {
                if (function_exists($filter[$i])) {
                    $data = $filter[$i]($data);
                }
            }
        }
        return $data;
    }

    //客户端IP
    public function ip($type = 0)
    {
        $type = intval($type);
        //保存客户端IP地址
        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $ip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $ip = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $ip = getenv("HTTP_X_FORWARDED_FOR");
            } else if (getenv("HTTP_CLIENT_IP")) {
                $ip = getenv("HTTP_CLIENT_IP");
            } else {
                $ip = getenv("REMOTE_ADDR");
            }
        }
        $long = ip2long($ip);
        $clientIp = $long ? [$ip, $long] : ["0.0.0.0", 0];

        return $clientIp[$type];
    }


    public function isWeiXin()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'MicroMessenger') === false) {
            return false;
        } else {
            return true;
        }
    }

    //https请求
    public function isHttps()
    {
        if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
            return true;
        } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            return true;
        }

        return false;
    }

    //手机判断
    public function isMobile()
    {
        $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
        $mobile_browser = '0';
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $mobile_browser++;
        }
        if ((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') !== false)) {
            $mobile_browser++;
        }
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            $mobile_browser++;
        }
        if (isset($_SERVER['HTTP_PROFILE'])) {
            $mobile_browser++;
        }
        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
        $mobile_agents = [
            'w3c ',
            'acs-',
            'alav',
            'alca',
            'amoi',
            'audi',
            'avan',
            'benq',
            'bird',
            'blac',
            'blaz',
            'brew',
            'cell',
            'cldc',
            'cmd-',
            'dang',
            'doco',
            'eric',
            'hipt',
            'inno',
            'ipaq',
            'java',
            'jigs',
            'kddi',
            'keji',
            'leno',
            'lg-c',
            'lg-d',
            'lg-g',
            'lge-',
            'maui',
            'maxo',
            'midp',
            'mits',
            'mmef',
            'mobi',
            'mot-',
            'moto',
            'mwbp',
            'nec-',
            'newt',
            'noki',
            'oper',
            'palm',
            'pana',
            'pant',
            'phil',
            'play',
            'port',
            'prox',
            'qwap',
            'sage',
            'sams',
            'sany',
            'sch-',
            'sec-',
            'send',
            'seri',
            'sgh-',
            'shar',
            'sie-',
            'siem',
            'smal',
            'smar',
            'sony',
            'sph-',
            'symb',
            't-mo',
            'teli',
            'tim-',
            'tosh',
            'tsm-',
            'upg1',
            'upsi',
            'vk-v',
            'voda',
            'wap-',
            'wapa',
            'wapi',
            'wapp',
            'wapr',
            'webc',
            'winw',
            'winw',
            'xda',
            'xda-',
        ];
        if (in_array($mobile_ua, $mobile_agents)) {
            $mobile_browser++;
        }
        if (strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false) {
            $mobile_browser++;
        }
        // Pre-final check to reset everything if the user is on Windows
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false) {
            $mobile_browser = 0;
        }
        // But WP7 is also Windows, with a slightly different characteristic
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false) {
            $mobile_browser++;
        }
        if ($mobile_browser > 0) {
            return true;
        } else {
            return false;
        }
    }
}