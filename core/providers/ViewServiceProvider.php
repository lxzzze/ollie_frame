<?php


namespace core\providers;

use core\View;

class ViewServiceProvider implements ServiceProviderInterface
{
    //注册视图服务
    public function register()
    {
        app()->bind('view',function (){
            return new View();
        },true);
    }

    //加载视图服务
    public function boot()
    {

    }
}