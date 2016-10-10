<?php namespace core\view;

class Tag extends TagBase
{
    /*
     * block 块标签
     * level 嵌套层次
     * @var array
     */
    public $tags = [
        'php' => ['block' => true, 'level' => 1],
        'list' => ['block' => true, 'level' => 2],
        'include' => ['block' => false],
        'editor' => ['block' => false]
    ];

    /**
     * php标签解析
     */
    public function _php($tag, $content)
    {
        return '<? ' . $content . ' ?>';
    }

    /**
     * list标签解析 循环输出数据集
     */
    public function _list($tag, $content)
    {
        $name = $tag['name']; //变量
        $k = !empty($tag['k']) ? $tag['k'] : 'k';
        $i = !empty($tag['i']) ? $tag['i'] : 'i';
        $v = !empty($tag['v']) ? $tag['v'] : 'v';//name名去除$
        $empty = isset($tag['empty']) ? $tag['empty'] : "<tr class='center aligned'><td>" . lang('Empty') . "</td></tr>";//默认值
        // 允许使用函数设定数据集 <list name="$:fun('arg')"></list>
        $parseStr = '<? ';
        if (0 === strpos($name, ':')) {
            $parseStr .= '$_result=' . substr($name, 1) . ';';
            $name = '$_result';
        }
        $parseStr .= 'if(is_array(' . $name . ')): $' . $i . ' = 0;';
        if (isset($tag['length']) && '' != $tag['length']) {
            $parseStr .= ' $_list = array_slice(' . $name . ',' . $tag['offset'] . ',' . $tag['length'] . ',true);';
        } elseif (isset($tag['offset']) && '' != $tag['offset']) {
            $parseStr .= ' $_list = array_slice(' . $name . ',' . $tag['offset'] . ',null,true);';
        } else {
            $parseStr .= ' $_list = ' . $name . ';';
        }
        $parseStr .= 'if( count($_list)==0 ) : echo "' . $empty . '" ;';
        $parseStr .= 'else: ';
        $parseStr .= 'foreach($_list as $' . $k . '=>$' . $v . '): ';
        $parseStr .= '++$' . $i . ';?>';
        $parseStr .= $content;
        $parseStr .= '<? endforeach; endif; else: echo "' . $empty . '" ;endif; ?>';

        if (!empty($parseStr)) {
            return $parseStr;
        }
        return;
    }

    /**
     * 加载模板文件
     */
    public function _include($tag, $content, &$view)
    {
        return $view->fetch($this->replaceConst($tag['file']));
    }

    /**
     * editor标签解析 插入可视化编辑器
     * 格式： <editor id="editor" name="remark">
     * @access public
     * @param array $tag 标签属性
     * @return string|void
     */
    public function _editor($tag, $content, &$view)
    {
        $id = !empty($tag['id']) ? $tag['id'] : '_editor';
        $name = $tag['name'];
        $width = !empty($tag['width']) ? $tag['width'] : '100%';
        $height = !empty($tag['height']) ? $tag['height'] : '500px';
        $content = $tag['content'];
        $parseStr = '<link rel="stylesheet" type="text/css" href="' . config('app.admin') . '/editor/themes/default/default.css">';
        $parseStr .= '<script charset="utf-8" src="' . config('app.admin') . '/editor/kindeditor.js"></script>';
        $parseStr .= '<script charset="utf-8" src="' . config('app.admin') . '/editor/lang/zh-CN.js"></script>';
        if (IS_AJAX) {
            $parseStr .= '<script type="text/javascript">
            KindEditor.create(\'#' . $id . '\', {
                    width:\'' . $width . '\',
                    height:\'' . $height . '\',
                    uploadJson : \'' . url('upload/upload') . '\',
                    fileManagerJson : \'' . url('upload/manage') . '\',
                    allowFileManager : true,
                    afterBlur:function(){this.sync();}
                });
            </script>';
        } else {
            $parseStr .= '<script type="text/javascript">
            KindEditor.ready(function(K){
                K.create(\'#' . $id . '\', {
                    width:\'' . $width . '\',
                    height:\'' . $height . '\',
                    uploadJson : \'' . url('upload/upload') . '\',
                    fileManagerJson : \'' . url('upload/manage') . '\',
                    allowFileManager : true,
                    afterBlur:function(){this.sync();}
                });
            });
            </script>';
        }
        $parseStr .= '<textarea id="' . $id . '" name="' . $name . '" >' . $content . '</textarea>';
        return $parseStr;
    }
}