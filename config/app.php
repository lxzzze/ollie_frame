<?php


return [
    'name' => 'ollie',
    'db' => [
        'name' => 'test'
    ],


    'providers' => [
        \core\providers\RoutingServiceProvider::class,
        \core\providers\ViewServiceProvider::class,
        \core\providers\ResponseServiceProvider::class,
        \core\providers\RequestServiceProvider::class,
        \core\providers\LogServiceProvider::class,
        \core\providers\DBServiceProvider::class
    ]

];