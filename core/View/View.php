<?php namespace Core\View;

use Exception;

//视图处理
class View
{
    private $app = '';

    function __construct($app)
    {
        $this->app = $app;
    }

    //模板变量集合
    protected static $vars = [];

    //模板数据
    protected $data = [];

    //共享变量集合
    public static $shares = [];

    //模版文件
    public $template;

    //编译文件
    public $compile;

    //去除空白
    public $strip_space = false;

    //gzip压缩
    public $gzip = false;

    //共享数据
    public function share($key, $val)
    {
        self::$shares[$key] = $val;
        return $this;
    }

    //赋值数据
    public function assign($data)
    {
        $this->data = $data;
        return $this;
    }

    /*
     * 渲染模板
     * @param string $template 模板
     * @param int $expire 过期时间
     * @return bool|string
     * @throws Exception
     */
    public function render($template, $expire = 0)
    {
        if (!DEBUG) {
            $expire = 1;
            $this->strip_space = true;
            $this->gzip = true;
        }
        //缓存有效
        if ($expire > 0 && $cache = Cache::dir(ROOT_PATH . '/data/view/cache')->get($_SERVER['REQUEST_URI'])) {
            $content = $cache;
        } else {
            $content = $this->fetch($template);
            if ($expire > 0) {
                //缓存
                if (!Cache::dir(ROOT_PATH . '/data/view/cache')->set($_SERVER['REQUEST_URI'], $content, $expire)) {
                    throw new Exception("创建缓存失效");
                }
            }
        }
        $this->app['Hook']->listen('csrf', $content);
        //获取解析结果
        die($content);
    }

    /*
     * 获取模板数据
     * @param string $template 模板
     * @return bool|string
     */
    public function fetch($template = '')
    {
        //模板文件
        if (!$this->template = $this->getTemplateFile($template)) {
            return false;
        }

        //编译文件
        $this->compile = ROOT_PATH . '/data/view/compile/' . md5($this->template) . '.php';

        //编译文件
        $this->compileFile();

        //变量赋值
        if (is_array($this->data)) {
            foreach ($this->data as $k => $v) {
                self::$vars[$k] = $v;
            }
        }

        //共享变量
        if (is_array(self::$shares)) {
            foreach (self::$shares as $k => $v) {
                self::$vars[$k] = $v;
            }
        }

        //释放变量到全局
        if (!empty(self::$vars)) {
            extract(self::$vars, EXTR_OVERWRITE);
        }

        //获取解析结果
        ob_start();
        require($this->compile);
        $content = ob_get_clean();
        //获取解析结果
        return $content;
    }

    /*
     * 获取模板文件
     * @param $file 模板文件
     * @return bool|string
     * @throws Exception
     */
    public function getTemplateFile($file)
    {
        if (!is_file($file)) {
            if (defined('MODULE')) {
                //模块视图文件夹
                $f = APP_PATH . '/' . MODULE . '/' . CONTROLLER . '/' . ($file ?: ACTION) . config('view.prefix');
                if (is_file($f)) {
                    return $f;
                }
                $f = APP_PATH . '/' . MODULE . '/' . $file . config('view.prefix');
                if (is_file($f)) {
                    return $f;
                }
            } else {
                //路由中使用回调函数执行View::render()时，因为没有MODULE
                $f = config('view.path') . '/' . $file . config('view.prefix');
                if (is_file($f)) {
                    return $f;
                }
            }
        }
        if (DEBUG) {
            throw new Exception("模板不存在:$f");
        } else {
            return false;
        }
    }

    /*
     * 编译文件
     */
    private function compileFile()
    {
        $status = DEBUG || !file_exists($this->compile) || !is_file($this->compile) || (filemtime($this->template) > filemtime($this->compile));
        if ($status) {
            //创建编译目录
            $dir = dirname($this->compile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            //执行文件编译
            $compile = new Compile($this);
            $content = $compile->run();
            file_put_contents($this->compile, $content);
        }
    }
}