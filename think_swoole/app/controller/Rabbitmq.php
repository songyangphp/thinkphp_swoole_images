<?php

namespace app\controller;

use app\BaseController;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * rabbitmq 消息中间件
 */
class Rabbitmq extends BaseController
{
    /**
     * 模拟生产者
     * @return mixed
     * @throws \Exception
     */
    public function producer()
    {
        $type = $this->request->get('type',1,'intval');
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

        // 关闭信道和连接
        $channel->close();
        $connection->close();
        return 'success';
    }

    /**
     * 直连模式
     * @param $channel
     * @return void
     */
    private function directType(AMQPChannel $channel)
    {
        $messageBody = 'Hello World!['.time().']'; //消息内容
        $exchangeName = 'test_exchange'; //交换机名字
        $routingKey = 'test_routing_key'; //路由键

        // 声明一个 exchange
        $channel->exchange_declare($exchangeName,'direct',false,true,false);

        // 创建一条消息
        $message = new AMQPMessage($messageBody,['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);

        // 将消息发布到 exchange
        $channel->basic_publish($message, $exchangeName, $routingKey);

        echo " [x] Sent '" . $messageBody . "‘ to exchange ’test_exchange’ with routing key ’" . $routingKey . "‘\n";
    }

    /**
     * 主题模式-发布&订阅
     * @param $channel
     * @return void
     */
    private function topicType(AMQPChannel $channel)
    {
        $exchangeName = 'topic_exchange'; //交换机名字

        // 声明一个 topic 类型的 exchange
        $channel->exchange_declare($exchangeName,'topic',false,true,false);

        // 定义不同主题的消息
        $messages = [
            ['routing_key' => 'usa.news', 'body' => '美国新闻消息'],
            ['routing_key' => 'europe.weather', 'body' => '欧洲天气预报'],
            ['routing_key' => 'usa.weather', 'body' => '美国天气预报'],
            ['routing_key' => 'chinese.weather', 'body' => '中国天气预报'],
            ['routing_key' => 'asia.sports.basketball.news', 'body' => '亚洲篮球体育新闻'],
            ['routing_key' => 'news', 'body' => '通用新闻'],
            ['routing_key' => 'usa.news.sports', 'body' => '美国体育新闻'],
        ];

        // 发送消息
        foreach($messages as $msg){
            // 创建消息
            $message = new AMQPMessage($msg['body'],[
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]);

            // 发送
            $channel->basic_publish($message, $exchangeName, $msg['routing_key']);
            echo " [x] Sent '" . $msg['body'] . "' with routing key '" . $msg['routing_key'] . "'\n";
        }
    }
}