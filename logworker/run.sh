#!/bin/sh

# upload Discuz Android
#nohup ./runscript "php ./bin/upload.php 1 1" &

# run pack Discuz IOS
CUR_DIR=$(cd `dirname $0`; pwd)
kill -9 `ps aux | grep 'start_log.php -d' | grep $CUR_DIR |grep -v grep |awk '{print $2}'`
nohup php $CUR_DIR/bin/start_log.php -d &





