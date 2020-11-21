<?php


if (!function_exists('app')){
    //获取app容器服务
    function app($name = null){
        if (!$name){
            return \core\Container::getContainer();
        }
        return \core\Container::getContainer()->get($name);
    }
}

if (!function_exists('config')){
    //获取配置文件信息
    function config($name = null){
        if (!$name){
            return null;
        }
        return app('config')->get($name);
    }
}

if (!function_exists('env1')){
    //获取env配置文件信息
    function env1($name = null,$default = null){
        if (!$name){
            return null;
        }
        if (isset($_ENV[$name])){
            return $_ENV[$name];
        }
        return $default;
    }
}


if (!function_exists('view')){
    //渲染视图
    function view($path,$params = []){
        $view = app('view')->render($path,$params);
        app('response')->getBody()->write($view);
        return app('response');
    }
}

if (!function_exists('response')){
    //返回响应
    function response($data,$status = 200){
        app('response')->getBody()->write($data);
        return app('response')->withStatus($status);
    }
}