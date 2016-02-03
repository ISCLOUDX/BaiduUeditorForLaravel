<?php
/**
 * Created by PhpStorm.
 * User: Ling
 * Date: 16/2/1
 * Time: 下午11:05
 */
Route::group(['middleware'=>['web','auth']],function(){

    Route::any('/api/ueditor','iscms\ueditor\BaiduEditorService@init');

});
