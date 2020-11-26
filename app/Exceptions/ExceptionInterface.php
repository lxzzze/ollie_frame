<?php


namespace App\Exceptions;


interface ExceptionInterface
{
    //错误处理
    public function handle($exception);
}