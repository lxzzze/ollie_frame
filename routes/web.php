<?php

$router->map('GET', '/', \App\Controller\TestController::class.'::index');
$router->map('GET', '/about', \App\Controller\TestController::class.'::about');


