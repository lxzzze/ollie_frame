<?php

namespace App\Controller;

use App\Model\Activity;
use League\Route\Http\Exception\NotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use think\facade\Db;


class TestController
{

    public function index(ServerRequestInterface $request)
    {
        $str = '123';
        return view('index',compact('str'));
    }

    public function about(ServerRequestInterface $request)
    {

    }
}