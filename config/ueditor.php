<?php

return [
    /**
     * 是否开启本地储存
     */
    'LocalSave'=>true,
    /**
     * 是否开启远程储存
     */
    'Remote'=>true,
    /**
     * 远程储存调用方式
     * 远程储存使用的是Laravel自带的FileSystem,
     * 您可以随意使用您filesystems中定义好的传输方式,
     * 不会产生任何的不兼容
     */
    'RemoteServer'=>[
        'image'=>'oss',
        'file' =>'oss',
        'video'=>'oss',
     ],

    /**
     * 这里可以定义您返回本地地址还是远程地址
     */
    'useRemoteUrl'=>true,
    /**
     * 后置修饰符
     */
    'useModifier'=>false,
    /**
     * 自定义储存路径,例如 "/Upload"
     */
    'Path'=>'/uploads',
    /**
     * 自定义文件是否需要按照时间创建文件夹
     * @controller Carbon::now()->format('您定义参数的格式');
     * 如果您不需要时间创建那么将此项设置为false即可关闭
     * @url 您可以访问 http://carbon.nesbot.com/
     */
    "DataFolder"=>'Y-m-d',
    /**
     * 是否开启UserId加密
     * 本包始终依赖 User 登陆状态才可以进行上传操作
     * 秋綾建议您保持这里的加密开启,当然也可以关闭
     */
    'isEncrypt'=>true,
    /**
     * 定义返回图片地址到编辑器时携带的前缀
     * 如果您想编辑器返回的数据直接使用您远程服务器上的地址,
     * 您可以在这里配置为 http(s)://domain.demo
     */
    'imageUrlPrefix'=>env('imageUrlPrefix','http(s)://domain.com'),
    /**
     * 定义返回视频地址到编辑器时携带的前缀
     * 如果您想编辑器返回的数据直接使用您远程服务器上的地址,
     * 您可以在这里配置为 http(s)://domain.demo
     */
    'videoUrlPrefix'=>env('videoUrlPrefix','http(s)://domain.com'),
    /**
     * 定义返回文件地址到编辑器时携带的前缀
     * 如果您想编辑器返回的数据直接使用您远程服务器上的地址,
     * 您可以在这里配置为 http(s)://domain.demo
     */
    'fileUrlPrefix' =>env('fileUrlPrefix','http(s)://domain.com'),

    'imageSize'=>'20480000',
    'videoSize'=>'102400000',
    'fileSize'=>'102400000',


    //稍后进行分组
    "imageAllowType"=>[".png", ".jpg", ".jpeg", ".gif", ".bmp"],
    "videoAllowType"=>[".mp4", ".flv"],
    "fileAllowType"=>[".rar", ".zip"],

];