<?php

namespace app\controller;

use app\BaseController;
use Swoole\Coroutine\WaitGroup;

//协程用例
class Coroutine extends BaseController
{
    public function demo()
    {
        //下面的实例我们将创建两个协程 第一个协程模拟耗时3秒 第二个协程模式耗时5秒 如果是传统面向过程模式 则总耗时可达8秒 利用协程 处理时间缩短至5秒
        //result数组模拟了两个协程之间程序执行顺序
        //协程1 -> 0
        //协程2 -> 0
        //协程1 -> 1
        //协程2 -> 
        //协程1 -> 2
        //协程2 -> 2 ==== 此时协程1已执行完毕
        //协程2 -> 3
        //协程2 -> 4 ==== 此时协程2已执行完毕
        //核心原理是遇到IO阻塞 交出cpu控制权 继续处理其他逻辑
        $start_time = time();
        $wg = new WaitGroup(); //通过WaitGroup让主进程等待所有协程运行完毕以后再执行

        $result = [];

        $wg->add(); //协程数量+1
        go(function () use ($wg, &$result){
            for ($i = 0; $i < 3; $i++){
                sleep(1);
                $result[] = $i."协程1";
            }
            $wg->done();//本协程任务完成
        });

        $wg->add(); //协程数量+1
        go(function () use ($wg, &$result){
            for ($i = 0; $i < 5; $i++){
                sleep(1);
                $result[] = $i."协程2";
            }
            $wg->done();//本协程任务完成
        });

        $wg->wait(); //总线程
        dump("程序总耗时".($start_time-time())."秒");
        dump($result);
    }
}