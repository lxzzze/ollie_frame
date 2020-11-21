<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface;

class TestController
{

    public function index(ServerRequestInterface $request)
    {
        $str = '123';
        return view('index',compact('str'));
    }

    public function about(ServerRequestInterface $request)
    {
        dd('123');
    }
}