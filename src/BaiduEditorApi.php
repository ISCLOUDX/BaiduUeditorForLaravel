<?php namespace iscms\ueditor;





use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

interface BaiduEditorApi
{
    //编辑器请求入口,不可修改
    public function init(Request $request);

}