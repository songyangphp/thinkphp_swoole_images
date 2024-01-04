<?php

//用swoole实现一个tcp服务 并支持异步任务处理
function logWright($log)
{
    file_put_contents(__DIR__."/../../runtime/swoole_log.txt",date('Y-m-d H:i:s')."=>".$log,FILE_APPEND);
}

$server = new \Swoole\Server('0.0.0.0', 9502);
//设置异步任务的工作进程数量
$server->set([
    'task_worker_num' => 1 //这里为了测试多任务的处理, 特地只设置一个, 实际上我们要根据任务数量设置多个进程
]);

$server->on('Start', function($server){
    logWright('服务器开启'.PHP_EOL);
    echo 'Swoole AsyncTask TCP server started at'.date('Y-m-d H:i:s').PHP_EOL;
});

//监听连接进入事件
$server->on('Connect', function($server, $fd){
    logWright("======================开始：".$fd."======================".PHP_EOL);
    //$fd: 客户端连接的唯一标识, int
    logWright('有连接接入 fd = '.$fd.PHP_EOL);//写入日志
    $server->send($fd, 'Welcome, your ID is: '.$fd.PHP_EOL);
});

//收到任务, 并投递异步任务 (此回调函数在worker进程中执行)
$server->on('Receive', function($server, $fd, $reactor_id, $data){
    //$reactor_id: TCP连接所在的Reactor线程ID, int
    //$data: 收到的数据内容，可能是文本或者二进制内容
    $data = trim($data);
    logWright('收到任务 fd = '.$fd.' 接收的数据是 '.$data.PHP_EOL);//写入日志
    echo date('Y-m-d H:i:s').' Client '.$fd.' 接收的数据是: '.$data.PHP_EOL;

    //投递异步任务
    $task_id = $server->task($data);

    $server->send($fd, 'Your task id is '.$task_id.' for : '.$data.PHP_EOL);

    echo 'Dispatch AsyncTask from client '.$fd.': id='.$task_id.PHP_EOL;
    logWright('投递异步任务 fd = '.$fd.': task_id = '.$task_id.PHP_EOL);
});

//处理异步任务 (此函数在task进程中执行)
$server->on('Task', function($server, $task_id, $reactor_id, $data){
    logWright('开始处理异步任务'.$data.': task_id = '.$task_id.PHP_EOL);

    sleep(5);//休眠5秒钟
    $strResult = time() % 2 == 0 ? '当前时间戳是双数' : '当前时间戳是单数';//我们随机一个结果

    //返回执行任务的结果
    $server->finish($data.' -> '.$strResult);
});

//处理异步任务的结果,可选(忽略结果) (此回调函数在worker进程中执行)
$server->on('Finish', function($server, $task_id, $data){
    logWright('处理结果： ('.$data.')  task_id = '.$task_id.' 异步任务处理完成'.PHP_EOL);
    echo 'AsyncTask[id='.$task_id.'] 处理结果：('.$data.') 完成时间 '.date('Y-m-d H:i:s').PHP_EOL;
    logWright("======================结束：".$task_id."======================".PHP_EOL);
});

//监听连接关闭事件
$server->on('Close', function($server, $fd){
    echo date('Y-m-d H:i:s').' Client closed: fd='.$fd.PHP_EOL;
    logWright(date('Y-m-d H:i:s').' 连接关闭 fd='.$fd.PHP_EOL);
});

$server->start();