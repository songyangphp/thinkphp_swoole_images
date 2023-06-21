## 介绍
* 基于thinkphp6+swoole的镜像源码
* 系统：Linux centOS-7 容器：docker
* php版本：php7.3.33
* 支持composer拓展
* 支持redis拓展
* 支持swoole拓展
* 支持swoole全部特性 异步任务投递处理已封装 为解决耗时程序处理问题 开箱即用
* 使用nginx作为web服务
* 可选kafka服务，mqtt服务
* 即可选择swoole作为服务，也可选择传统web配置
* 纯粹的thinkphp6框架 默认单应用模式 强制路由
* demo中包含了协程用例，异步任务投递用例，websocket用例


## 使用方式及注意事项(保姆级教程)
* 安装docker windows系统直接安装docker desktop会自动集成docker和docker-compose
* 构建镜像 镜像中包含了系统镜像，镜像地址在阿里云ACR镜像管理平台管理维护 镜像地址：registry.cn-beijing.aliyuncs.com/order_sae/order_device_pay_api:v1.0
* 配置nginx反向代理swoole监听使用（conf/conf.d/api.pay.device.songyang.com.conf和dev.api.pay.device.songyang.com.conf）
* 配置nginx域名指向（conf/conf.d/test.device.songyang.com.com.conf）
* 以上配置的域名可根据自己的业务进行修改
* crond目录为定时任务配置
* 配置.env环境变量
* 开放9501端口 容器端口80指向宿主机85端口 开放443端口(https) 在docker-compose.yml文件中配置
* 本地调试时 mysql的地址为宿主机局域网ip .env中配置
* 准备工作完毕 开始构建镜像
* 根目录下 执行命令docker-compose build
* 等待镜像构建完成后 执行名称docker-compose up启动容器
* docker desktop中Containers可以看到正在运行的容器
* 镜像下拉如果失败，请配置加速域名（使用阿里云镜像加速服务），docker desktop->settings->DockerEngine的json中加入如下代码
* "registry-mirrors": [
  "https://b2wyggjg.mirror.aliyuncs.com"
  ]
* 访问nginx配置中的域名:端口号
* 如果不成功检查上述流程是否未执行成功或遗漏
* 在config/swoole.php配置端口及热更新等配置

## 阿里云云效及流水线相关配置
* 核心目标是为了更容易的发布部署到serverless或其他K8s服务


## 目前存在的问题
* 构建镜像时，DOCKERFILE中不可执行composer install命令，这样就导致了无法忽略vendor目录

***

#### by：火车王 songyangphp@github.com
#### 本人水平有限 不足之处还请大佬指教
#### 2023-6-19日更新