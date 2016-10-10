<?php namespace core\response;

class Response
{
    /*
     * 发送HTTP 状态码
     *
     * @param $code
     */
    public function sendHttpStatus($code)
    {
        $status = [
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',

            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',

            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',

            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded',
        ];

        if (isset($status[$code])) {
            header('HTTP/1.1 ' . $code . ' ' . $status[$code]);
            header('Status:' . $code . ' ' . $status[$code]);
        }
    }

    /*
     * Ajax输出
     *
     * @param        $data 数据
     * @param string $type 数据类型 text html json
     */
    public function ajax($data, $type = "JSON")
    {
        $type = strtoupper($type);
        switch ($type) {
            case "HTML" :
            case "TEXT" :
                $_data = $data;
                break;
            default :
                //JSON处理
                header('Content-Type: application/json');
                $_data = json_encode($data);
        }
        echo $_data;
        exit;
    }

    public function ajaxSuccess($info, $url = '', $time = 1000)
    {
        $this->ajax(['info' => $info, 'url' => $url, 'status' => 1, 'time' => $time]);
    }

    public function ajaxError($info, $url = '', $time = 1000)
    {
        $this->ajax(['info' => $info, 'url' => $url, 'status' => 0, 'time' => $time]);
    }
}