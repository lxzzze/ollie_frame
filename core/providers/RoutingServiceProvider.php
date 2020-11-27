<?php
namespace core\providers;

use App\middleware\position1Middleware;
use App\middleware\TestMiddleware;
use League\Route\Router;

class RoutingServiceProvider implements ServiceProviderInterface
{

    protected $mapRoutes = [
        'mapWebRoutes'
    ];

    //注册路由服务
    public function register()
    {
        $this->registerRouter();
    }

    private function registerRouter()
    {
        app()->bind('router',function (){
            return new Router();
        },true);
    }



    public function boot()
    {
        $router = app('router');
        foreach ($this->mapRoutes as $route){
            call_user_func($this->$route(),$router);
        }
        app('router')->middleware(new position1Middleware());

        app('router')->middleware(new TestMiddleware());

    }


    public function mapWebRoutes()
    {
        return function ($router){
            require_once 'routes/web.php';
        };
    }

}