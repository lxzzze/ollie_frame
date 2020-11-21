<?php


return [

    'default' => 'mysql',


    'connections' => [

        'mysql' => [
            'driver' => 'mysql',
            'host' => env1('DB_HOST','127.0.0.1'),
            'port' => env1('DB_PORT','3306'),
            'database' => env1('DB_DATABASE','demo'),
            'username' => env1('DB_USERNAME','root'),
            'password' => env1('DB_PASSWORD',''),
            'options' => [

            ]
        ]
    ]
];
