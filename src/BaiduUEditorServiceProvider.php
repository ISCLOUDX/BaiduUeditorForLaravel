<?php namespace iscms\Ueditor;
use Illuminate\Support\ServiceProvider;

class BaiduUEditorServiceProvider extends ServiceProvider
{

    public function boot()
    {
        include __DIR__.'/../routes.php';

        $this->publishes([
            __DIR__ . '/../config/ueditor.php' => config_path('ueditor.php'),
        ]);
    }


    public function register()
    {
        $this->app->bind('iscms\ueditor\BaiduEditorApi', 'iscms\ueditor\BaiduEditorService');
    }




}