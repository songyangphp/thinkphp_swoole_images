<?php
declare (strict_types = 1);

namespace app\command;

use PhpAmqpLib\Channel\AMQPChannel;
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
    /**
     * 配置指令
     * @return void
     */
    protected function configure()
    {
        // 指令配置
        $this->setName('rabbitConsume')
            ->addOption('type','t',Option::VALUE_REQUIRED,'请选择类型1直连 2主题',1)
            ->setDescription('rabbitmq 模拟消费者');
    }

    /**
     * 执行指令
     * @param Input $input
     * @param Output $output
     * @return mixed
     * @throws \Exception
     */
    protected function execute(Input $input, Output $output)
    {
        $type = intval($input->getOption('type'));
        $typeArray = [
            1 => 'direct', // 直连模式
            2 => 'topic' // 主题模式-发布&订阅
        ];
        if(!isset($typeArray[$type])){
            return 'type fail';
        }

        // 创建连接到 RabbitMQ 服务器
        $connection = new AMQPStreamConnection('host.docker.internal', 5672, 'admin', 'admin321');
        $channel = $connection->channel();

        $function = $typeArray[$type]."type";
        $this->$function($channel);

        // 关闭连接
        $channel->close();
        $connection->close();
    }

    /**
     * 直连模式
     * @param $channel
     * @return void
     */
    private function directType(AMQPChannel $channel)
    {
        $exchangeName = 'test_exchange'; // exchange的名称

        // 声明 Exchange
        $channel->exchange_declare($exchangeName, 'direct', false, true, false);

        // 声明一个队列 让服务器为我们选择一个随机名称
        list($queueName,,) = $channel->queue_declare('', false, false, true,false);

        // 将队列绑定到exchange 并指定binding Key
        $bindKey = 'test_routing_key';
        $channel->queue_bind($queueName, $exchangeName, $bindKey);

        echo " [*] Waiting for messages on queue ’" . $queueName . "’. To exit press CTRL+C\n";

        // 定义消息处理回调函数
        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
        };

        // 开始消费队列中的消息
        $channel->basic_consume($queueName, '', false, true, false, false, $callback);
        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }


    /**
     * 主题模式-发布&订阅
     * @param $channel
     * @return void
     */
    private function topicType(AMQPChannel $channel)
    {
        $exchangeName = 'topic_exchange'; // exchange的名称

        // 声明队列（让服务器生成随机队列名）
        list($queueName, ,) = $channel->queue_declare("", false, false, true, false);

        // 绑定路由键：接收所有天气预报相关的消息（只需要改bindKey(routingKey)，就可以实现接收不同bindKey的消息）
        $bindingKey = '#.weather';
        $channel->queue_bind($queueName, $exchangeName, $bindingKey);

        echo " [*] Consumer 1 waiting for messages with binding key '" . $bindingKey . "'. To exit press CTRL+C\n";

        $callback = function ($msg) {
            echo " [x] Consumer 1 Received '" . $msg->body . "' with routing key '" . $msg->get('routing_key') . "'\n";
        };

        $channel->basic_consume($queueName, '', false, true, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
}
