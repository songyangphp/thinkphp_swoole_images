<?php
declare (strict_types = 1);

namespace app\command;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

/**
 * rabbitmq模拟消费者
 */
class RabbitConsume extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('rabbitConsume')->setDescription('rabbitmq 模拟消费者');
    }

    protected function execute(Input $input, Output $output)
    {

        // 创建连接到 RabbitMQ 服务器
        $connection = new AMQPStreamConnection('host.docker.internal', 5672, 'admin', 'admin321');
        $channel = $connection->channel();

        // 声明队列

        echo " [*] Waiting for messages. To exit press CTRL+C\n";

        // 回调函数，用于处理接收到的消息
        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
            // 模拟任务处理
            sleep(3);
            echo " [x] Done\n";
            // 手动确认消息已经处理
            $msg->ack();
        };

        // 告诉 RabbitMQ 在同一时间不要发送多于一条消息给一个消费者
        $channel->basic_qos(0, 1, null);

        // 告诉 RabbitMQ 使用回调函数来接收消息，并手动确认消息
        $channel->basic_consume('task_queue', '', false, false, false, false, $callback);

        // 等待消息进入队列
        while ($channel->is_consuming()) {
            $channel->wait();
        }

        // 关闭连接
        $channel->close();
        $connection->close();

    }
}
