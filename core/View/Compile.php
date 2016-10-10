<?php namespace core\view;

class Compile
{
    //视图对象
    private $view;
    //分隔符
    private $left;
    private $right;
    //block块
    private $block = [];
    //模板编译内容
    private $content;

    //构造函数
    function __construct(&$view)
    {
        $this->view = $view;
        $this->left = Config::get('view.tag_left');
        $this->right = Config::get('view.tag_right');
    }

    /*
     * 运行编译
     * @return string
     */
    public function run()
    {
        //模板内容
        $this->content = file_get_contents($this->view->template);

        //解析布局
        $this->layoutParse();

        //解析标签
        $this->tagsParse();

        //解析全局变量与常量
        $this->globalParse();

        //去除空白
        $this->stripSpace();

        //压缩文件
        $this->gzip();

        //保存编译文件
        return $this->content;
    }

    /**
     * 解析布局
     * @param array $content
     * @return string
     */
    private function layoutParse()
    {
        $find = preg_match('/' . $this->left . 'layout\sname=[\'"](.+?)[\'"]\s*?' . $this->right . '/is', $this->content, $matches);
        if ($find) {
            $content = str_replace($matches[0], '', $this->content);
            preg_replace_callback('/' . $this->left . 'block\sname=[\'"](.+?)[\'"]\s*?' . $this->right . '(.*?)' . $this->left . '\/block' . $this->right . '/is', [$this, 'parse_block'], $content);
            $this->content = $this->replace_block(file_get_contents($this->view->getTemplateFile($matches[1])));
        } else {
            preg_replace_callback('/' . $this->left . 'block\sname=[\'"](.+?)[\'"]\s*?' . $this->right . '(.*?)' . $this->left . '\/block' . $this->right . '/is', function ($match) {
                $this->content = stripslashes($match[2]);
            }, $this->content);
        }
    }

    /**
     * 记录当前页面中的block标签
     * @access private
     * @param string $name block名称
     * @param string $content 模板内容
     * @return string
     */
    private function parse_block($name, $content = '')
    {
        if (is_array($name)) {
            $content = $name[2];
            $name = $name[1];
        }
        $this->block[$name] = $content;
        return '';
    }

    private function replace_block($content)
    {
        static $parse = 0;
        $reg = '/(' . $this->left . 'block\sname=[\'"](.+?)[\'"]\s*?' . $this->right . ')(.*?)' . $this->left . '\/block' . $this->right . '/is';
        if (is_string($content)) {
            do {
                $content = preg_replace_callback($reg, [$this, 'replace_block'], $content);
            } while ($parse && $parse--);
            return $content;
        } elseif (is_array($content)) {
            if (preg_match('/' . $this->left . 'block\sname=[\'"](.+?)[\'"]\s*?' . $this->right . '/is', $content[3])) {
                $parse = 1;
                $content[3] = preg_replace_callback($reg, array($this, 'replace_block'), "{$content[3]}{$this->left}/block{$this->right}");
                return $content[1] . $content[3];
            } else {
                $name = $content[2];
                $content = $content[3];
                $content = isset($this->block[$name]) ? $this->block[$name] : $content;
                return $content;
            }
        }
    }

    /*
     * 解析标签
     */
    private function tagsParse()
    {
        //标签库
        $tags = Config::get('view.tags');
        $tags[] = 'core\view\Tag';

        //解析标签
        foreach ($tags as $tag) {
            $obj = new $tag();
            $this->content = $obj->parse($this->content, $this->view);
        }
    }

    /*
     * 解析全局变量与常量
     */
    private function globalParse()
    {
        //处理{}
        $this->content = preg_replace('/(?<!@)\{\{(.*?)\}\}/is', '<?=\1?>', $this->content);

        //$this->content = preg_replace_callback('/(?<!@)\{\{(.*?)\}\}/is', [$this, 'varParse'],$this->content);

        //处理@{}
        $this->content = preg_replace('/@(\{\{.*?\}\})/i', '\1', $this->content);
    }

    private function varParse($var)
    {

        return $var;
    }

    /*
     * 去除空白
     */
    private function stripSpace()
    {
        if ($this->view->strip_space) {
            $content = preg_replace(array('~>\s+<~', '~>(\s+\n|\r)~'), array('><', '>'), $this->content);
            $this->content = str_replace('?><?', '', $content);
        }
    }

    /**
     * Gzip数据压缩传输 如果客户端支持
     * @param string $content
     * @return string
     */
    private function gzip()
    {
        if (!headers_sent() && extension_loaded("zlib") && strstr($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") && $this->view->gzip) {
            $this->content = gzencode($this->content, 9);
            header('Content-Encoding:gzip');
            header('Vary:Accept-Encoding');
            header('Content-Length:' . strlen($this->content));
        }
    }
}