<?php
/**
 * Created by PhpStorm.
 * User: Ling
 * Date: 16/2/3
 * Time: 上午11:35
 */

namespace iscms\ueditor;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Hashids;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Ixudra\Curl\Facades\Curl;

class UploadServer extends Controller
{
    /**
     * 声明公共变量
     * @var array
     */
    public $default, $request, $userId, $name, $folder,$allowType,$path;
    /**
     * 上传服务构造器
     * 初始化相关变量配置项
     * UploadServer constructor.
     */
    public function __construct()
    {
        if (config('ueditor.isEncrypt')) {
            $this->userId = Hashids::encode($this->userId);
        }
        $this->userId=Auth::User()->id;
        $this->default = [
            "imageActionName" => "uploadImage", /* 执行上传图片的action名称 */
            "imageFieldName" => "file", /* 提交的图片表单名称 */
            "imageMaxSize" => config('ueditor.imageSize'), /* 上传大小限制，单位B */
            "imageAllowFiles" => config('ueditor.imageAllowType'), /* 上传图片格式显示 */
            "imageCompressEnable" => false, /* 是否压缩图片,默认是true */
            "imageInsertAlign" => "none", /* 插入的图片浮动方式 */
            "imageUrlPrefix" => '', /* 图片访问路径前缀 */
            /* 涂鸦图片上传配置项 */
            "scrawlActionName" => "uploadScrawl", /* 执行上传涂鸦的action名称 */
            "scrawlFieldName" => "file", /* 提交的图片表单名称 */
            "scrawlPathFormat" => "", /* 上传保存路径,可以自定义保存路径和文件名格式 */
            "scrawlMaxSize" => config('ueditor.imageSize'), /* 上传大小限制，单位B */
            "scrawlUrlPrefix" => "", /* 图片访问路径前缀 */
            "scrawlInsertAlign" => "none",
            /* 截图工具上传 */
            "snapscreenActionName" => "uploadimage", /* 执行上传截图的action名称 */
            "snapscreenPathFormat" => "", /* 上传保存路径,可以自定义保存路径和文件名格式 */
            "snapscreenUrlPrefix" => "", /* 图片访问路径前缀 */
            "snapscreenInsertAlign" => "none", /* 插入的图片浮动方式 */

            /* 抓取远程图片配置 */
            "catcherLocalDomain" => ["127.0.0.1", "localhost", "img.baidu.com"],
            "catcherActionName" => "catchImage", /* 执行抓取远程图片的action名称 */
            "catcherFieldName" => "source", /* 提交的图片列表表单名称 */
            "catcherPathFormat" => "", /* 上传保存路径,可以自定义保存路径和文件名格式 */
            "catcherUrlPrefix" => "", /* 图片访问路径前缀 */
            "catcherMaxSize" => config('ueditor.imageSize'), /* 上传大小限制，单位B */
            "catcherAllowFiles" => config('ueditor.imageAllowType'), /* 抓取图片格式显示 */

            /* 上传视频配置 */
            "videoActionName" => "uploadVideo", /* 执行上传视频的action名称 */
            "videoFieldName" => "file", /* 提交的视频表单名称 */
            "videoPathFormat" => "", /* 上传保存路径,可以自定义保存路径和文件名格式 */
            "videoUrlPrefix" => "", /* 视频访问路径前缀 */
            "videoMaxSize" => config('ueditor.videoSize'), /* 上传大小限制，单位B，默认100MB */
            "videoAllowFiles" => config('ueditor.videoAllowType'), /* 上传视频格式显示 */

            /* 上传文件配置 */
            "fileActionName" => "uploadfile", /* controller里,执行上传视频的action名称 */
            "fileFieldName" => "file", /* 提交的文件表单名称 */
            "filePathFormat" => "", /* 上传保存路径,可以自定义保存路径和文件名格式 */
            "fileUrlPrefix" => "", /* 文件访问路径前缀 */
            "fileMaxSize" => config('ueditor.fileSize'), /* 上传大小限制，单位B，默认50MB */
            "fileAllowFiles" => config('ueditor.fileAllowType'), /* 上传文件格式显示 */
        ];
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImages()
    {
        $result=$this->makeImages();
        if ($result) {
            return response()->json([
                "state" => "SUCCESS",
                "url" => "$result",
                "title" => "",
                "original" => "",
            ]);
        }
        return response()->json([
            "state" => "保存文件失败",
        ]);
    }

    public function CatchImage()
    {

    }
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadScrawl()
    {
        if ($this->checkRequest()){
            $this->path=$this->assemblyPath();
            $result=$this->createImage();
            if ($result) {
                return response()->json([
                    "state" => "SUCCESS",
                    "url" => "$result",
                    "title" => "",
                    "original" => "",
                ]);
            }
        }
        return response()->json([
            "state" => "保存文件失败",
        ]);
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadVideo()
    {
        if ($this->checkRequest()) {
            $this->path=$this->assemblyPath();
            $path=$this->request->file('file')->getRealPath();
            $result=$this->uploadProcess(File::get("{$path}"));
            if ($result) {
                return response()->json([
                    "state" => "SUCCESS",
                    "url" => "$result",
                    "title" => "",
                    "original" => "",
                ]);
            }
        }
        return response()->json([
            "state" => "上传失败",
        ]);
    }


    public function catchImages()
    {
        $source = $this->request->source;
        $item = array(
            "state" => "",
            "url" => "",
            "size" => "",
            "title" => "",
            "original" => "",
            "source" => ""
        );

        $list = array();
        foreach ($source as $imgUrl) {
            $return_img = $item;
            $return_img['source'] = $imgUrl;
            $imgUrl = htmlspecialchars($imgUrl);
            $imgUrl = str_replace("&amp;", "&", $imgUrl);

            if (strpos($imgUrl, "http") !== 0) {
                $return_img['state'] = "错误的链接";
                array_push($list, $return_img);
                continue;
            }
            $result = Curl::to("$imgUrl")->withOption('REFERER', 'http://www.baidu.com')->get();




            $file = $result->get('content');

            $info = $result->get('info');

            $type=last(explode('/',$info['content_type']));

            $this->path=$this->assemblyPath();
            $path=sys_get_temp_dir().'/'.md5(uniqid());
            $content=Image::make($file)->save($path);
            if ($url=$this->uploadProcess(File::get("{$path}"))) {
                $return_img['state'] = 'SUCCESS';
                $return_img['url'] = $url;
                array_push($list, $return_img);
            } else {
                array_push($list, ['state' =>"无法写入文件"]);
            }
        }
        return response()->json(array(
            'state' => count($list) ? 'SUCCESS' : 'ERROR',
            'list' => $list
        ));

    }


    /**
     * @return bool|string
     */
    private function makeImages()
    {
        if ($this->checkRequest()){
            $this->path=$this->assemblyPath();
            return $this->createImage();
        };
        return false;
    }

    /**
     * @return bool|string
     */
    private function createImage()
    {
        if ($this->request->file == base64_encode(base64_decode($this->request->file)))
        {
            $path=sys_get_temp_dir().'/'.md5(uniqid());
            Image::make(base64_decode($this->request->file))->save($path);
            return $this->uploadProcess(File::get("{$path}"));
        }else{
            $imageContent=$this->request->file('file');
            $path=$imageContent->getRealPath();
            $content=Image::make($path)->save($path);
            if ($content)
            {
                return $this->uploadProcess(File::get("{$path}"));
            }
        }
        return false;
    }

    /**
     * @param $content
     * @return bool|string
     */
    private function uploadProcess($content)
    {
        $remoteResult=false;
        $localResult=false;
        if (config('ueditor.Remote')) {
            if ($this->request->file('file')){
                $mime=$this->request->file('file')->getClientMimeType();
            }else{
                $mime=null;
            }
            switch($mime)
            {
                case str_contains($mime,'video'):
                    $remoteType = config('ueditor.RemoteServer.video');
                    break;
                case str_contains($mime,'file'):
                    $remoteType = config('ueditor.RemoteServer.file');
                    break;
                default:
                    $remoteType = config('ueditor.RemoteServer.image');
                    break;
            }
            $remoteResult=Storage::disk("{$remoteType}")->put("{$this->path}", $content);
        }
        if (config('ueditor.LocalSave')) {
            if ($this->request->file('file')){
                $localResult=$this->request->file('file')->move(public_path($this->folder), basename($this->path));
            }else{
                $localResult=Storage::disk('local')->put("{$this->path}",$content);
            }
        }
        if ($remoteResult || $localResult)
        {
            return $this->backUrl();
        }
        return false;
    }

    /**
     * @return string
     */
    private function backUrl()
    {
        $url=$this->path;
        $modifier = '';
        if ((config('ueditor.Remote') && config('ueditor.useRemoteUrl') || !config('ueditor.LocalSave'))) {
            if (config('ueditor.useModifier')) {
                $modifier = config('ueditor.useModifier');
            }


            if ($this->request->file('file')){
                $mime=$this->request->file('file')->getClientMimeType();
            }else{
                $mime=null;
            }
            switch($mime)
            {
                case str_contains($mime,'video'):
                    $prefix=config('ueditor.videoUrlPrefix');
                    break;
                case str_contains($mime,'file'):
                    $prefix=config('ueditor.fileUrlPrefix');
                    break;
                default:
                    $prefix=config('ueditor.imageUrlPrefix');
                    break;
            }

            return "{$prefix}{$url}{$modifier}";
        }
        return "{$url}{$modifier}";
    }

    /**
     * @return bool
     */
    private function checkRequest()
    {

        if ($this->request->file('file')){
            $mime=$this->request->file('file')->getClientMimeType();
        }else if ($this->request->file == base64_encode(base64_decode($this->request->file))){
            return true;
        }else{
            return false;
         }

        switch($mime)
        {
            case str_contains($mime,'image'):
                $AllowType = str_replace(".", '', collect(config('ueditor.imageAllowType'))->implode(','));
                break;
            case str_contains($mime,'video'):
                $AllowType = str_replace(".", '', collect(config('ueditor.videoAllowType'))->implode(','));
                break;
            case str_contains($mime,'file'):
                $AllowType = str_replace(".", '', collect(config('ueditor.fileAllowType'))->implode(','));
                break;
            default:
                return false;
                break;
        }

        $this->allowType=$AllowType;
        return $this->AllowType();
    }

    /**
     * @return bool
     */
    private function allowType()
    {
        $v = Validator::make($this->request->only('file'), [
            'file' => "required|mimes:{$this->allowType}",
        ]);
        if ($v->fails()) {
            return false;
        }
        return $this->checkSize();
    }

    /**
     * @return string
     */
    private function assemblyPath()
    {
        return "{$this->createFolder()}{$this->createName()}";
    }

    /**
     * @return bool
     */
    private function checkSize()
    {

        if ($this->request->file('file')->isValid()){
            $size=$this->request->file('file')->getClientSize();
            $mime=$this->request->file('file')->getClientMimeType();
            switch($mime)
            {
                case str_contains($mime,'image'):
                    $maxSize=config('ueditor.imageSize');
                    break;
                case str_contains($mime,'video'):
                    $maxSize=config('ueditor.videoSize');
                    break;
                default:
                    $maxSize=config('ueditor.fileSize');
                    break;
            }
            if ($size>$maxSize)
            {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    private function createFolder()
    {
        $data_folder = '';
        if (config('ueditor.DataFolder')) {
            $time = Carbon::now()->format(config('ueditor.DataFolder'));
            $data_folder = "/{$time}/";
        }
        if ($this->request->file('file')){
            $mime=$this->request->file('file')->getClientMimeType();
            switch($mime)
            {
                case str_contains($mime,'video'):
                    $type_folder='videos';
                    break;
                case str_contains($mime,'files'):
                    $type_folder='files';
                    break;
                default:
                    $type_folder='images';
                    break;
            }
        }else{
            $type_folder='images';
        }
        $custom_folder = config('ueditor.Path');
        $this->folder = "{$custom_folder}/{$this->userId}/{$type_folder}{$data_folder}";
        return $this->folder;
    }

    /**
     * @return string
     */
    private function createName(){
        return "{$this->createFileName()}.{$this->createFileExt()}";
    }

    /**
     * @return string
     */
    private function createFileName()
    {
        return $random_filename = md5(uniqid().$this->userId);
    }

    /**
     * @return string
     */
    private function createFileExt()
    {
        if ($this->request->file('file'))
        {
            $ext=$this->request->file('file')->getClientOriginalExtension();
            return $ext;
        }
        return 'png';
    }

}