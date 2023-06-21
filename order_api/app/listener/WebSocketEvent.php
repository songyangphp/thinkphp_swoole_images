<?php

namespace app\listener;

use think\Container;
use think\facade\Log;
use think\swoole\Websocket;

//采用websocketIO方式
class WebSocketEvent
{
    public $websocket = null;

    public function __construct(Container $container)
    {
        $this->websocket = $container->make(Websocket::class);
    }

    /**
     * 事件监听处理 可以在此分发业务逻辑
     * @param $event
     */
    public function handle($event)
    {
        Log::info($event);
        Log::info("发送者：".$this->websocket->getSender());
        
        $func = $event['type'];
        $this->$func($event);
    }

    /**
     * 测试类型
     * @param $event
     */
    public function test($event)
    {
        $fd = $this->websocket->getSender();//当前客户端fd
        $this->websocket->broadcast()->emit('testcallback', ['fd' => $fd, 'massage' => $event['data'][0]['massage']]); //给所有人发送消息
        $this->websocket->to($fd)->emit('testcallback', ['fd' => $fd, 'massage' => $event['data'][0]['massage']]); //给自己发送消息
    }

    /**
     * 加入房间
     * @param $event
     */
    public function join($event)
    {
        $data = $event->data;
        $this->websocket->join($data[0]['room']);
    }

    /**
     * 离开房间
     * @param $event
     */
    public function leave($event)
    {
        $data = $event->data;
        $this->websocket->leave($data[0]['room']);
    }

    public function __call($name,$arguments)
    {
        $this->websocket->emit('testcallback', ['msg' => '不存在的请求类型:'.$name]);
    }
}