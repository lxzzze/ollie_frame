<?php


namespace core\providers;


use core\DB;

class DBServiceProvider implements ServiceProviderInterface
{

    public function register()
    {
        return app()->bind('DB',function (){
            return new DB();
        });
    }

    public function boot()
    {
        // TODO: Implement boot() method.
    }
}