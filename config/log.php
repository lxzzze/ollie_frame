<?php


return [

    'default' => 'single',

    'channel' => [

        'single' => [
            //日志驱动为文件
            'driver' => 'file',
            'path' => FRAME_BASE_PATH.'/storage/logs/ollie.log'
        ],
        'daily' => [
            'driver' => 'file',
            'path' => FRAME_BASE_PATH.'/storage/logs/'.date('Y-m-d').'.log'
        ]
    ]

];