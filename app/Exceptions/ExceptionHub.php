<?php


namespace App\Exceptions;


class ExceptionHub implements ExceptionInterface
{
    //处理异常类
    protected $handleException;

    //错误异常中心处理
    public function handle($exception)
    {
        $this->createExceptions(get_class($exception));
        if (!$this->handleException){
            //未知异常
            $this->notFoundExceptions();
            exit();
        }
        //异常处理
        $this->handleException->handle($exception);
    }

    //工厂函数,创建处理异常类
    public function createExceptions($className)
    {
        $explode = explode('\\',$className);
        $exceptionName = last($explode);
        $handleExceptionName = 'App\Exceptions\\'.$exceptionName;
        if (class_exists($handleExceptionName)){
            $this->handleException = new $handleExceptionName();
        }
    }

    public function notFoundExceptions()
    {
        echo '未知异常';
    }
}