<?php

namespace app\controller;

use app\BaseController;
use think\App;
use think\Exception;
use think\facade\Cache;

//redis测试类 使用redis实现一个简易秒杀系统 可能有缺陷 仅供学习参考
class RedisTest extends BaseController
{
    const QUEUE_NAME_GOODS_NUM = 'goods:num'; //hash 库存数量 也可以不用hash 用其他类型也可以 我这里使用hash的目的是不创建过多的redis key
    const QUEUE_NAME_GOODS_USER = 'goods:user:'; //set 抢到的用户id
    const QUEUE_NAME_SUCCESS_NUM = 'goods:success'; //hash 抢成功用户的数量
    const QUEUE_NAME_FAIL_NUM = 'goods:fail'; //hash 抢失败用户的数量
    
    //单例 
    protected $redis = null;
    
    public function __construct(App $app)
    {
        //获取redis操作句柄
        if(is_null($this->redis)){
            $this->redis = Cache::store('redis')->handler();
        }
        parent::__construct($app);
    }

    /**
     * 初始化库存
     * @param $good_id 
     * @param $good_num
     * @return void
     */
    public function initGoodNum($good_id, $good_num)
    {
        //设置库存初始值 商品id 商品数量
        $this->redis->hset(self::QUEUE_NAME_GOODS_NUM,$good_id,$good_num);
    }

    /**
     * 秒杀下单
     * @param $good_id
     * @param $user_id
     * @return bool
     */
    public function flashSale($good_id, $user_id)
    {
        //检查是否还有库存
        $good_num = $this->redis->hget(self::QUEUE_NAME_GOODS_NUM,$good_id);
        if($good_num < 1){ //库存不足 秒杀失败数量+1
            $this->redis->hincrby(self::QUEUE_NAME_FAIL_NUM,$good_id,+1);
            throw new Exception("没库存了");
        }
        
        //检查该用户是否重复下单
        $goods_has_user = $this->redis->sismember(self::QUEUE_NAME_GOODS_USER.$good_id,$user_id);
        if($goods_has_user){ //重复下单 秒杀失败数量+1
            $this->redis->hincrby(self::QUEUE_NAME_FAIL_NUM,$good_id,+1);
            throw new Exception("一个用户只允许抢购一次");
        }
        
        //1.库存充足 库存-1
        $residue_good_num = $this->redis->hincrby(self::QUEUE_NAME_GOODS_NUM,$good_id,-1);
        //抢完后 剩余的库存如果小于0 则秒杀失败
        if($residue_good_num < 0){
            //将库存补1 否则可能会造成超发 库存负值
            $this->redis->hincrby(self::QUEUE_NAME_GOODS_NUM,$good_id,+1);
            $this->redis->hincrby(self::QUEUE_NAME_FAIL_NUM,$good_id,+1);
            throw new Exception("没库存了");
        }
        
        //2.将抢到的用户存入到set中
        $this->redis->sadd(self::QUEUE_NAME_GOODS_USER.$good_id,$user_id);
        //3.秒杀成功数量+1
        $this->redis->hincrby(self::QUEUE_NAME_SUCCESS_NUM,$good_id,+1);
        
        return true;
    }

    /**
     * 获取参与秒杀用户总数
     * @param $good_id
     * @return string
     */
    public function getTakePartUserTotal($good_id)
    {
        $success_num = (int)$this->redis->hget(self::QUEUE_NAME_SUCCESS_NUM,$good_id);
        $fail_num = (int)$this->redis->hget(self::QUEUE_NAME_FAIL_NUM,$good_id);
        return bcadd($success_num,$fail_num);
    }
    
    //可以模拟并发秒杀
    //测验结果 使用ab压测
    //本地百级并发 数据一致性完好
    public function index()
    {
        $user_id = rand(1000,9999); //模拟用户id
        try {
            $result = $this->flashSale(1,$user_id);
            if($result === true){
                echo "恭喜你，抢到了";
            }
        }catch (Exception $exception){
            echo "很抱歉，".$exception->getMessage();
        }
    }
}