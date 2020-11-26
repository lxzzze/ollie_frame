<?php


namespace App\Exceptions;


class NotFoundException implements ExceptionInterface
{

    public function handle($exception)
    {
        echo '路由未匹配成功';
    }
}