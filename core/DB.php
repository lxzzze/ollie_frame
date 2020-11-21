<?php


namespace core;


use core\DB\Mysql;

class DB
{
    //DB连接
    protected $connect;
    //
    protected $host;

    protected $port;

    protected $database;
    //数据库名称
    protected $username;
    //数据库密码
    protected $password;
    //驱动
    protected $driver;
    //数据库连接实例
    protected $connectInstance;

    public function __construct()
    {
        $this->init();
    }

    //修改连接配置
    public function connection($name = null)
    {
        $this->init($name);
        return $this->getDBInstance();
    }

    //初始化连接配置
    public function init($name = null)
    {
        if (!$name){
            //获取默认链接配置
            $this->connect = config('database.default');
        }else{
            $this->connect = $name;
        }
        $this->host = config('database.connections.'.$this->connect.'.host');
        $this->port = config('database.connections.'.$this->connect.'.port');
        $this->database = config('database.connections.'.$this->connect.'.database');
        $this->username = config('database.connections.'.$this->connect.'.username');
        $this->password = config('database.connections.'.$this->connect.'.password');
        $this->driver = config('database.connections.'.$this->connect.'.driver');
    }

    //获取DB操作实例
    public function getDBInstance()
    {
        $connectionClass = null; // 链接处理的类

        switch ($this->driver){
            case 'mysql':
                $connectionClass = Mysql::class;
                $dsn = sprintf('%s:host=%s;dbname=%s', $this->driver, $this->host, $this->database);
                $pdo = new \PDO($dsn,$this->username,$this->password);
                return new $connectionClass($pdo);
        }

    }

    public function __call($method, $arguments)
    {
        return $this->getDBInstance()->$method(...$arguments);
    }
}