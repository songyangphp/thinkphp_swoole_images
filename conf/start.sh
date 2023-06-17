#!/bin/bash
crond &
/usr/bin/php-fpm &
/usr/bin/php /data/www/think swoole start &
/usr/local/tengine/sbin/nginx -c /usr/local/tengine/conf/nginx.conf

# /usr/bin/php /data/www/think task start --taskcommand dealWithTaskOrder &
# /usr/bin/php /data/www/think task start --taskcommand dealWithBlack