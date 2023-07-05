FROM registry.cn-beijing.aliyuncs.com/linux_php73/songyang_linux_php73:v1.0


RUN yum -y update && yum -y install vim tree htop tmux net-tools telnet wget curl supervistor autoconf git gcc gcc-c++ pcre pcre-devel zlib zlib-devel openssl openssl-devel php73-php-process php73-php-gmp
RUN yum install crontabs -y
# 创建用户
RUN groupadd tengine
RUN useradd -g tengine tengine

# 定义Tengine版本号
ENV VERSION 2.3.3

# 下载并解压文件
RUN mkdir -p /usr/local/src/
ADD http://tengine.taobao.org/download/tengine-$VERSION.tar.gz /usr/local/src
RUN tar -xvf /usr/local/src/tengine-$VERSION.tar.gz -C /usr/local/src/

# 创建安装目录
ENV TENGINE_HOME /usr/local/tengine
RUN mkdir -p $TENGINE_HOME

# 进入解压目录
WORKDIR /usr/local/src/tengine-$VERSION

# 编译安装
RUN ./configure \
    --user=tengine \
    --group=tengine \
    --prefix=$TENGINE_HOME \
#    --with-http_concat_module  \
#    --with-http_stub_status_module \
#    --with-http_upstream_consistent_hash_module
    --with-pcre \
    --with-http_ssl_module \
    --with-http_realip_module \
    --without-mail_pop3_module \
    --without-mail_imap_module \
    --with-http_gzip_static_module && \
    make && make install


# 备份Tengine的配置文件
RUN mkdir -p $TENGINE_HOME/conf/conf.d/
RUN mv $TENGINE_HOME/conf/nginx.conf $TENGINE_HOME/conf/nginx.conf.default
COPY ./conf/nginx.conf   $TENGINE_HOME/conf/
COPY ./conf/sysctl.conf  /etc/
COPY ./conf/conf.d/ $TENGINE_HOME/conf/conf.d/
RUN  echo 'ulimit -SHn 65535' >> /etc/profile

# 设置环境变量
ENV PATH $PATH:$TENGINE_HOME/sbin

#kafka安装操作
#COPY ./conf/confluent.repo /etc/yum.repos.d/
#RUN yum -y install librdkafka-devel
#COPY ./conf/rdkafka.so  /opt/remi/php73/root/usr/lib64/php/modules/
#RUN echo "extension=rdkafka.so" >> /etc/opt/remi/php73/php.ini

# 安装composer
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
RUN composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/

# 创建WebApp目录
RUN mkdir -p /data/www/
RUN chown -R tengine:tengine /data/www/


COPY ./order_api/ /data/www/
# 复制环境变量
COPY ./conf/env/.env /data/www/.env
# 复制php.ini
COPY ./conf/php.ini /etc/opt/remi/php73/

# 设置默认工作目录
WORKDIR /data/www/

# composer安装拓展

# RUN composer install

# 暴露端口
EXPOSE 80 443 9092 9501

# 清理压缩包与解压文件
RUN rm -rf /usr/local/src/tengine*
RUN rm -rf /data/www/.env
RUN rm -rf /etc/opt/remi/php73/php.d/15-xdebug.ini
RUN rm -rf /etc/opt/remi/php73/php.d/40-apcu.ini
RUN rm -rf /etc/opt/remi/php73/php.d/50-apc.ini

ADD ./conf/start.sh /start.sh
RUN chmod 755 /start.sh
ENTRYPOINT  ["/start.sh"]

## 安装composer拓展
#RUN cd /data/www/
#RUN composer install