<?php

$router->map('GET', '/', \App\Controller\TestController::class.'::index')->middleware(new \App\middleware\position2Middle());
$router->map('GET', '/about', \App\Controller\TestController::class.'::about');


