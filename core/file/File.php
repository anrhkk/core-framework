<?php namespace core\file;

class File
{
    //上传类型
    public $rename = FALSE;
    //上传文件大小
    protected $type;
    //上传路径
    protected $size;
    //上传Src
    protected $path;
    //错误信息
    protected $src;
    protected $error = '';

    function __construct()
    {
        //上传路径
        $this->path = __PUBLIC__ . Config::get('upload.path');
        //上传Src
        $this->src = Config::get('upload.path');
        //上传类型
        $this->type = Config::get('upload.type');
        //允许大小
        $this->size = Config::get('upload.size');
        //允许重命名
        $this->rename = Config::get('upload.rename');
    }

    /*
     * 上传
     * @param  [type] $fieldName [字段名]
     * @return [type]            [description]
     */
    public function upload($fieldName = null)
    {
        if (!$this->createDir()) {
            return false;
        }
        $files = $this->format($fieldName);

        $uploadedFile = [];
        //验证文件
        if (!empty($files)) {
            foreach ($files as $v) {
                $info = pathinfo($v ['name']);
                $v["ext"] = isset($info ["extension"]) ? $info['extension'] : '';
                $v['filename'] = isset($info['filename']) ? $info['filename'] : '';

                if (!$this->checkFile($v)) {
                    continue;
                }
                $upFile = $this->save($v);
                if ($upFile) {
                    $uploadedFile[] = $upFile;
                }
            }
        }

        return $uploadedFile;
    }

    //设置上传类型
    private function createDir()
    {
        if (!is_dir($this->path) && !mkdir($this->path, 0755, true)) {
            throw new \Exception("上传目录创建失败");
        }

        return true;
    }

    //设置上传大小
    private function format($fieldName)
    {
        if ($fieldName == null) {
            $files = $_FILES;
        } else if (isset($_FILES[$fieldName])) {
            $files[$fieldName] = $_FILES[$fieldName];
        }

        if (!isset($files)) {
            $this->error = '没有任何文件上传';
            return false;
        }
        $info = [];
        $n = 0;
        foreach ($files as $name => $v) {
            if (is_array($v['name'])) {
                $count = count($v['name']);
                for ($i = 0; $i < $count; $i++) {
                    foreach ($v as $m => $k) {
                        $info [$n] [$m] = $k [$i];
                    }
                    $info [$n] ['fieldname'] = $name; //字段名
                    $n++;
                }
            } else {
                $info [$n] = $v;
                $info [$n] ['fieldname'] = $name; //字段名
                $n++;
            }
        }
        return $info;
    }

    //设置上传目录
    private function checkFile($file)
    {
        if ($file ['error'] != 0) {
            $this->error($file ['error']);

            return false;
        }
        if (!in_array(strtolower($file['ext']), $this->type)) {
            $this->error = '文件类型不允许';

            return false;
        }
        if (strstr(strtolower($file['type']), "image") && !getimagesize($file['tmp_name'])) {
            $this->error = '上传内容不是一个合法图片';

            return false;
        }
        if ($file ['size'] > $this->size) {
            $this->error = '上传文件大于' . $this->getSize($this->size);

            return false;
        }

        if (!is_uploaded_file($file ['tmp_name'])) {
            $this->error = '非法文件';

            return false;
        }

        return true;
    }

    private function error($error)
    {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE :
                $this->error = '上传文件超过PHP.INI配置文件允许的大小';
                break;
            case UPLOAD_ERR_FORM_SIZE :
                $this->error = '文件超过表单限制大小';
                break;
            case UPLOAD_ERR_PARTIAL :
                $this->error = '文件只上有部分上传';
                break;
            case UPLOAD_ERR_NO_FILE :
                $this->error = '没有上传文件';
                break;
            case UPLOAD_ERR_NO_TMP_DIR :
                $this->error = '没有上传临时文件夹';
                break;
            case UPLOAD_ERR_CANT_WRITE :
                $this->error = '写入临时文件夹出错';
                break;
            default:
                $this->error = '未知错误';
        }
    }

    /*
     * 储存文件将上传文件整理为标准数组
     * @param string $file 储存的文件
     *
     * @return boolean
     */

    public function getSize($size, $decimals = 2)
    {
        switch (true) {
            case $size >= pow(1024, 3):
                return round($size / pow(1024, 3), $decimals) . " GB";
            case $size >= pow(1024, 2):
                return round($size / pow(1024, 2), $decimals) . " MB";
            case $size >= pow(1024, 1):
                return round($size / pow(1024, 1), $decimals) . " KB";
            default:
                return $size . 'B';
        }
    }

    private function save($file)
    {
        if ($this->rename) {
            $fileName = date('YmdHis') . mt_rand(1000, 9999) . "." . $file['ext'];
        } else {
            $fileName = $file['filename'] . "." . $file['ext'];
        }
        $filePath = $this->path . '/' . $fileName;
        $fileSrc = $this->src . '/' . $fileName;
        if (!move_uploaded_file($file ['tmp_name'], $filePath) && is_file($filePath)) {
            $this->error('移动临时文件失败');

            return false;
        }
        $_info = pathinfo($filePath);
        $arr = [];
        $arr['path'] = $filePath;
        $arr['src'] = $fileSrc;
        $arr['uptime'] = NOW;
        $arr['fieldname'] = $file['fieldname'];
        $arr['basename'] = $_info['basename'];
        $arr['filename'] = $_info['filename']; //新文件名
        $arr['name'] = $file['filename']; //旧文件名
        $arr['size'] = $file['size'];
        $arr['ext'] = $file['ext'];
        $arr['dir'] = $this->path;
        $arr['image'] = getimagesize($filePath) ? 1 : 0;

        return $arr;
    }

    public function delete($image)
    {
        return unlink(__PUBLIC__ . $image);
    }

    public function type($type)
    {
        $this->type = $type;
        return true;
    }

    public function size($size)
    {
        $this->size = $size;
        return true;
    }

    /*
     * 返回上传时发生的错误原因
     *
     * @return string
     */

    public function path($path)
    {
        $this->path = $path;
        return true;
    }

    /*
     * 根据大小返回标准单位 KB  MB GB等
     *
     * @return string
     */

    public function getError()
    {
        return $this->error;
    }
}