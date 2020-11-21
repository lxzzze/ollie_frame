<?php


namespace core\providers;

use core\Config;
use core\Container;
use Dotenv\Dotenv;

class ConfigServiceProvider implements ServiceProviderInterface
{
    //注册服务
    public function register()
    {
        app()->bind('config',function (){
            return new Config();
        },true);
    }

    //加载服务
    public function boot()
    {
        app('config');
    }

}