<?php

namespace app\listener;

use think\Container;
use think\facade\Log;
use think\swoole\Websocket;

//采用websocketIO方式 实现简易多人聊天室
class WebSocketEvent
{
    public $websocket = null;

    public function __construct(Container $container)
    {
        $this->websocket = $container->make(Websocket::class); //采用依赖注入的方式实例化websocket类
    }

    /**
     * 事件监听处理 监听客户端发送的请求 可以在此分发业务逻辑
     * @param $event
     */
    public function handle($event) //$event['type']请求类型 $event['data']请求数据
    {
        //将web发送的数据存入日志
        Log::info($event);
        Log::info("发送者：".$this->websocket->getSender());
        
        $func = $event['type'];
        $this->$func($event);
    }

    /**
     * 测试类型
     * @param $event
     */
    public function test($event) //h5页面监听testcallback事件 名称可自行修改 h5页面中的 socket.on('testcallback') 也需同步修改
    {
        $fd = $this->websocket->getSender();//当前客户端fd
        $this->websocket->broadcast()->emit('testcallback', ['fd' => $fd, 'massage' => $event['data'][0]['massage'], 'date' => date("Y-m-d H:i:s")]); //给所有人发送消息
        $this->websocket->to($fd)->emit('testcallback', ['fd' => $fd, 'massage' => $event['data'][0]['massage'], 'date' => date("Y-m-d H:i:s")]); //给自己发送消息
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