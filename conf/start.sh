#!/bin/bash
crond &
/usr/bin/php-fpm &
/usr/bin/php /data/www/think swoole start &
/usr/local/tengine/sbin/nginx -c /usr/local/tengine/conf/nginx.conf

# /data/www composer install