<?php
// 事件定义文件
return [
    'bind'      => [
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'swoole.task' => [
            app\listener\SwooleTask::class,
        ],
        'swoole.finish' => [
            app\listener\SwooleTaskFinish::class,
        ],

    ],

    'subscribe' => [
    ],
];