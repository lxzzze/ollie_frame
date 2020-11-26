<?php


namespace App\Exceptions;


class MethodNotAllowedException implements ExceptionInterface
{

    public function handle($exception)
    {
        echo '请求方法未被允许';
    }
}