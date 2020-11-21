<?php


namespace core;


use Dotenv\Dotenv;

class Config
{

    public function __construct()
    {
        //加载env文件
        $dotenv = Dotenv::createImmutable(FRAME_BASE_PATH);
        $dotenv->load();
    }

    //获取配置文件数据
    public function get($name)
    {
        $name = explode('.',$name);
        $file = $name[0];
        if (!isset($this->$file)){
            if (!file_exists(__DIR__.'/../config/'.$file.'.php')){
                return null;
            }
            $fileArr = require_once __DIR__.'/../config/'.$file.'.php';
            $this->$file = $fileArr;
        }else{
            $fileArr = $this->$file;
        }

        array_shift($name);
        while ($name && $fileArr){
            if (isset($fileArr[$name[0]])){
                $fileArr = $fileArr[$name[0]];
                array_shift($name);
            }else{
                return null;
            }
        }
        return $fileArr;
    }
}