<?php
/**
 * PowerBy http://www.iscloudx.com
 * 本版本写于2016/2/1
 */
namespace iscms\ueditor;

use Illuminate\Http\Request;

class BaiduEditorService extends UploadServer implements BaiduEditorApi
{
    /**
     * 文件配置
     * BaiduEditorService constructor.
     */


    /**
     * 百度编辑器统一入口
     * @param Request $request
     * @return mixed
     */

    public function init(Request $request)
    {
        if (!config('ueditor.LocalSave') && !config('ueditor.Remote')) {
            return response()->json(['state' => '您需要开启存储方式']);
        }
        $method = $request->action;
        $this->request = $request;
        switch ($method) {
            case "config":
                return $this->config();
                break;
            case "uploadImage":
                return $this->uploadImages();
                break;
            case "catchImage":
                return $this->catchImages();
                break;
            case "uploadScrawl":
                return $this->uploadScrawl();
                break;
            case "uploadVideo":
                return $this->UploadVideo();
                break;
            default:
                return response()->json(['info' => "您的上传操作无效", 'status' => 0, 'data' => null]);
                break;
        }
    }

    /**
     * 系统默认配置项
     * @return mixed
     */
    private function config()
    {
        $config = $this->default;
        return response()->json($config);
    }

}