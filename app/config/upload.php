<?php
return [
    //允许上传类型
    'type' => ['jpg', 'jpeg', 'gif', 'png', 'zip', 'rar', 'doc', 'txt'],
    //允许上传文件大小 单位B
    'size' => 2097152,
    //上传路径
    'path' => '/upload',
    //重命名
    'rename' => FALSE,
    //七牛云存储
    'bucket' => 'putao',
    'image_url' => 'http://image.putaosexy.com',
    'upload_url' => 'http://up.qiniu.com',
    'access_key' => 'EXOaviTG5_ARfHoSkeNnpFnhRiPVaWQ_OKSFh2j9',
    'secret_key' => 'A7qsRiWqD9S97-_nVpE1runPyGNBrRZ8DoFMijiS',
];