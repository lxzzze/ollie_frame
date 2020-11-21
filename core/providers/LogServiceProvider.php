<?php


namespace core\providers;



use core\Log;

class LogServiceProvider implements ServiceProviderInterface
{
    //注册日志服务
    public function register()
    {
        app()->bind('log',function (){
            return new Log();
        });
    }

    public function boot()
    {
        // TODO: Implement boot() method.
    }
}