<?php


namespace core\providers;


use Laminas\Diactoros\ServerRequestFactory;

class RequestServiceProvider implements ServiceProviderInterface
{

    public function register()
    {
        app()->bind('request',function (){
            return ServerRequestFactory::fromGlobals(
                $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
            );
        },true);
    }

    public function boot()
    {
        // TODO: Implement boot() method.
    }
}