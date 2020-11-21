<?php


namespace core\providers;


interface ServiceProviderInterface
{
    //注册服务
    public function register();

    //启用服务
    public function boot();
}