<?php


namespace core;

use duncan3dc\Laravel\BladeInstance;


class View
{
    protected $template;

    public function __construct()
    {
        // 设置视图路径 和 缓存路径
        $this->template = new BladeInstance(config('view.view_path'), config('view.cache_path'));
    }

    // 传递路径 和 参数
    public function render($path, $params = [])
    {
        return $this->template->render($path, $params);
    }

}