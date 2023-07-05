<?php
declare (strict_types = 1);

namespace app\listener;
use Swoole\Server;
use Swoole\Server\Task;
use think\Container;
use think\swoole\Table;

//在这里做异步任务分发 分发规则在config/task.php配置
class SwooleTask
{
    protected $server = null;
    protected $table = null;
    protected $key = '';
    protected $alias = [];

    public function __construct(Server $server, Container $container)
    {
        $this->server = $server;
        $this->table = $container->get(Table::class);
        $this->key = config('task.key');
        $this->alias = config('task.alias');
    }

    /**
     * 事件监听处理 分发给相应的任务处理类
     * @return mixed
     */
    public function handle(Task $task)
    {
        if (isset($task->data[$this->key]) && isset($this->alias[$task->data[$this->key]])) {
            $class = new $this->alias[$task->data[$this->key]]['class'];
            $methods = $this->alias[$task->data[$this->key]]['methods'];
            foreach ($methods as $method) {
                $class->$method($task->data);
            }
            if ($this->alias[$task->data[$this->key]]['finish']) {
                $task->finish($task->data);
            }
        } else {
            dump('未定义的task任务:' . json_encode($task->data, JSON_UNESCAPED_UNICODE));
        }
        return;
    }
}