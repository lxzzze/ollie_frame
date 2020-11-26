<?php


namespace core\providers;


use App\Exceptions\ExceptionHub;

class ExceptionServiceProvider implements ServiceProviderInterface
{
    //注册异常处理服务
    public function register()
    {
        return app()->bind('exception',function (){
            return new ExceptionHub();
        });
    }


    public function boot()
    {

    }

}