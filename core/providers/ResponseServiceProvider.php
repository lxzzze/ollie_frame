<?php


namespace core\providers;


use Laminas\Diactoros\Response;

class ResponseServiceProvider implements ServiceProviderInterface
{

    public function register()
    {
        app()->bind('response',function (){
            return new Response();
        },true);
    }

    public function boot()
    {
        // TODO: Implement boot() method.
    }
}