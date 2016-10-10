<?php namespace core\error;

//错误处理
class Error
{

    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function bootstrap()
    {
        //set_error_handler([$this, 'error']);
        //set_exception_handler([$this, 'exception']);
        //register_shutdown_function([$this, 'fatal']);
    }

    /**
     * 自定义错误处理
     * @access public
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     * @return void
     */
    public function error($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                ob_end_clean();
                $errorStr = "$errstr " . $errfile . " 第 $errline 行.";
                if (config('log.enable')) Log::write("[$errno] " . $errorStr, Log::ERROR);
                $this->halt($errorStr);
                break;
            default:
                $errorStr = "[$errno] $errstr " . $errfile . " 第 $errline 行.";
                $this->trace($errorStr, '', Log::NOTICE);
                break;
        }
    }

    /**
     * 自定义异常处理
     * @access public
     * @param mixed $e 异常对象
     */
    public function exception($e)
    {
        $error = [];
        $error['message'] = $e->getMessage();
        $error['file'] = $e->getFile();
        $error['line'] = $e->getLine();
        $error['trace'] = $e->getTraceAsString();
        if (config('log.enable')) Log::write($error['message'], Log::EXCEPTION);
        $this->halt($error);
    }

    // 致命错误捕获
    public function fatal()
    {
        Log::save();
        if ($e = error_get_last()) {
            switch ($e['type']) {
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    ob_end_clean();
                    $this->halt($e);
                    break;
            }
        }
    }

    /**
     * 错误输出
     * @param mixed $error 错误
     * @return void
     */
    public function halt($error)
    {
        $e = [];
        if (DEBUG) {
            //调试模式下输出错误信息
            if (!is_array($error)) {
                $trace = debug_backtrace();
                $e['message'] = $error;
                $e['file'] = $trace[0]['file'];
                $e['line'] = $trace[0]['line'];
                ob_start();
                debug_print_backtrace();
                $e['trace'] = ob_get_clean();
            } else {
                $e = $error;
            }
        } else {
            $e['message'] = is_array($error) ? $error['message'] : $error;
        }
        // 包含异常页面模板
        DEBUG && require CORE_PATH . '/error/view/exception.php';
        exit;
    }

    /*
     * trace 信息
     *
     * @param  string $value 变量
     * @param  string $label 标签
     * @param  string $level 日志级别(或者页面Trace的选项卡)
     * @param  boolean $record 是否记录日志
     *
     * @return void|array
     */
    public function trace($value = '[core]', $label = '', $level = 'DEBUG', $record = false)
    {
        static $trace = [];
        if ('[core]' === $value) {
            // 获取trace信息
            return $trace;
        } else {
            $info = ($label ? $label . ':' : '') . print_r($value, true);
            $level = strtoupper($level);
            if (IS_AJAX || $record) {
                Log::record($info, $level, $record);
            } else {
                if (!isset($trace[$level])) {
                    $trace[$level] = [];
                }
                $trace[$level][] = $info;
            }
        }
    }
}