<?php


namespace core;


use core\logDriver\file;

class Log
{
    //日志渠道
    protected $channel;
    //日志驱动
    protected $driver;
    //路径
    protected $path;
    //当前日志实体类
    protected $instance;

    public function __construct()
    {
        $this->channel = config('log.default');
        $this->driver = config('log.channel.'.$this->channel.'.driver');
        $this->path = config('log.channel.'.$this->channel.'.path');
        $this->getDriverInstance();
    }

    //重新定义日志渠道
    public function channel($name = null)
    {
        if (!$name){
            $this->channel = config('log.default');
            $this->driver = config('log.channel.'.$this->channel.'.driver');
            $this->path = config('log.channel.'.$this->channel.'.path');
        }else{
            $this->channel = $name;
            $this->driver = config('log.channel.'.$this->channel.'.driver');
            $this->path = config('log.channel.'.$this->channel.'.path');
        }
        $this->getDriverInstance();
        return $this;
    }

    //获取日志驱动实体类
    public function getDriverInstance()
    {
        if ($this->driver == 'file'){
            $this->instance = new file();
        }
    }

    public function info($message)
    {
        if ($this->driver == 'file'){
            $this->instance->info($message,$this->path);
        }
    }

}