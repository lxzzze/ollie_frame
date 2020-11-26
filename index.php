<?php

define('FRAME_BASE_PATH', __DIR__); // 框架目录

require __DIR__.'/vendor/autoload.php';

require_once __DIR__.'/core/Container.php';
//实例化容器(包括初始化服务)
$container = app();
try {
    //返回响应
    $response = app('router')->dispatch(app('request'));
    //将响应返回客户端
    (new Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
}catch (\Exception $exception){
    app('exception')->handle($exception);
}


