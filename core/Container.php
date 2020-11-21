<?php
namespace core;

use core\providers\ConfigServiceProvider;
use core\providers\RoutingServiceProvider;
use core\providers\ViewServiceProvider;

class Container
{
    //当前绑定的对象
    private $binds = [];

    //当前实例的对象数组
    private $instances = [];

    //当前容器类的实例
    private static $instance;

    public function __construct()
    {
        //将自己的实例添加到当前实例对象数组中
        $this->instances[Container::class] = $this;
        self::$instance = $this;
        //注册基础服务
        $this->registerBaseProvider();
        //注册框架功能服务提供者
        $this->registerFrameProvider();
        $this->boot();
    }

    //静态方法返回容器当前实例
    public static function getContainer()
    {
        return self::$instance ? self::$instance : new self();
    }

    //获取指定类的实例
    public function get($name,$real_args = [])
    {
        //检查实例是否存在,已存在则直接返回
        if (isset($this->instances[$name])){
            return $this->instances[$name];
        }
        //检查是否绑定该类和当前类是否存在
        if (!isset($this->binds[$name]) && !isset($this->instances[$name])){
            if (!class_exists($name,true)){
                throw new \InvalidArgumentException('class not exists');
            }
        }
        if (isset($this->binds[$name])){
            if (is_callable($this->binds[$name]['concrete'])){
                $instance = $this->call($this->binds[$name]['concrete'],$real_args);
            }else{
                $instance = $this->build($name,$real_args);
            }
        }else{
            $instance = $this->build($name,$real_args);
        }

        //是否为单例,将其对象添加到绑定数组中
        if ($this->binds[$name]['is_singleton'] = true){
            $this->instances[$name] = $instance;
        }
        return $instance;

    }


    //将对象名和创建对象的闭包添加到绑定对象数组
    public function bind($name,$concrete,$is_singleton = false)
    {
        if ($concrete instanceof \Closure) {
            $this->binds[$name] = ['concrete' => $concrete, "is_singleton" => $is_singleton];
        } else {
            if (!is_string($concrete) || !class_exists($concrete, true)) {
                throw new \InvalidArgumentException("value must be callback or class name");
            }
        }

        $this->binds[$name] = ['concrete' => $concrete, "is_singleton" => $is_singleton];
    }

    //调用闭包函数
    public function call(callable $callback,$real_args = [])
    {
        $refl_function = new \ReflectionFunction($callback);
        $parameters = $refl_function->getParameters();
        $parsed_args = [];
        if (count($parameters) > 0) {
            $parsed_args = $this->getDependencies($parameters,$real_args);
        }
        return $refl_function->invokeArgs($parsed_args);
    }


    //通过反射类创建类的实例
    public function build($class_name,$real_args = [])
    {
        $reflection = new \ReflectionClass($class_name);
        //获取类的构造函数
        $constructor = $reflection->getConstructor();
        if (is_null($constructor)){
            return $reflection->newInstance();
        }
        //获取构造函数的参数
        $parameters = $constructor->getParameters();
        //获取构造函数的依赖参数
        $dependencies = $this->getDependencies($parameters,$real_args);
        return $reflection->newInstanceArgs($dependencies);
    }

    //获取构建类所必要的参数信息
    public function getDependencies($parameters,$real_args = [])
    {
        $dependencies = [];
        foreach ($parameters as $parameter){
            if ($parameter->getClass() != null){
                //依赖类不存在
                if (!class_exists($parameter->getClass()->getName(),true)){
                    throw new \InvalidArgumentException('class not exists');
                }else{
                    $dependencies[] = $this->get($parameter->getClass()->getName());
                }
            }else{
                if (isset($real_args[$parameter->getName()])){
                    $dependencies[] = $real_args[$parameter->getName()];
                }else{
                    //获取参数默认值
                    $dependencies[] = $parameter->getDefaultValue();
                }
            }
        }
        return $dependencies;
    }

    //注册基础服务提供者
    public function registerBaseProvider()
    {
        (new ConfigServiceProvider())->register();
    }

    //注册框架功能服务提供者
    public function registerFrameProvider()
    {
        $providers = config('app.providers');
        foreach ($providers as $provider){
            (new $provider())->register();
        }
    }

    //调用服务提供者
    public function boot()
    {
        (new RoutingServiceProvider())->boot();
    }


}