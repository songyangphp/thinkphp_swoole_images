<?php
declare (strict_types = 1);

namespace app\listener;

use think\facade\Db;

class TestTask
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($event)
    {
        sleep(5);
        $msg = $event['data']['msg'];
        Db::name("test")->insert([
            "msg" => $msg,
            "create_date" => date("Y-m-d H:i:s",time())
        ]);
        return;
    }
}