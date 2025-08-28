<?php

namespace app\controller;

use app\BaseController;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


/**
 * rabbitmq 消息中间件
 */
class Rabbitmq extends BaseController
{
    public function send()
    {

        // 创建连接到 RabbitMQ 服务器
        $connection = new AMQPStreamConnection('host.docker.internal', 5672, 'admin', 'admin321');
        $channel = $connection->channel();

        // 声明队列
        $channel->queue_declare('task_queue', false, true, false, false);

        // 发送的消息内容
        $data = "Hello World!";
        $msg = new AMQPMessage($data, array('delivery_mode' => 2)); // 设置消息为持久化

        // 发布消息到队列
        $channel->basic_publish($msg, '', 'task_queue');

        echo " [x] Sent 'Hello World!'\n";

        // 关闭连接

        $channel->close();
        $connection->close();
    }
}