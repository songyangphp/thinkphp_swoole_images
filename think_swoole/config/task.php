<?php
return [
    //任务关键字提取 通过关键字找到分发类
    'key' => 'cmd',
    //任务别名
    'alias' => [
        'TestTask' => [ //名称
            'class' => \app\listener\TestTask::class, //调用类
            'methods' => [ //执行方法
                'handle',
            ],
            'finish' => false //是否触发task.finish执行完毕回调
        ]
    ]
];