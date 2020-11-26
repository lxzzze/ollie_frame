<?php


namespace core\providers;


use think\facade\Db;

class DBServiceProvider implements ServiceProviderInterface
{

    public function register()
    {
        // 数据库配置信息设置（全局有效）
        Db::setConfig(config('database'));
    }

    public function boot()
    {
        // TODO: Implement boot() method.
    }
}