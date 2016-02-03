<?php namespace iscms\Ueditor;
use Illuminate\Support\ServiceProvider;

class BaiduUEditorServiceProvider extends ServiceProvider
{

    public function boot()
    {
        include __DIR__.'/../routes.php';


        $this->loadViewsFrom(__DIR__.'/../vendor/views', 'ueditor');

        $this->publishes([
            __DIR__ . '/../config/ueditor.php' => config_path('ueditor.php'),
        ],'config');

        $this->publishes([
            __DIR__.'/../vendor/ueditor' => public_path('/ueditor'),
        ],'public');

    }

    public function register()
    {
        $this->app->bind('iscms\ueditor\BaiduEditorApi', 'iscms\ueditor\BaiduEditorService');
    }

}