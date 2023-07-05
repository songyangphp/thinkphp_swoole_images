<?php

namespace app\controller;

use app\BaseController;
use Swoole\Server;
use think\facade\Db;

//异步任务用例
class AsyncTask extends BaseController
{
    public function demo(Server $server)
    {
        //向swoole投递异步任务 采用thinkphp6的依赖注入特性
        //cmd => 选择处理任务的类
        //data => 传参
        //配置在config/task.php中配置
        //新进监听器建议使用thinkphp自带命令 php think make:listener [监听器类名] 默认使用类中的handle方法
        //以下为测试 先插入同步执行 异步等待5秒后再插入 模拟耗时操作
        $server->task(['cmd' => 'TestTask', 'data' => ['msg' => '异步执行']]);
        Db::name("test")->insert([
            "msg" => "同步执行",
            "create_date" => date("Y-m-d H:i:s",time())
        ]);
        return json(['msg' => '处理成功','date' => date("Y-m-d H:i:s",time())]);
    }
}