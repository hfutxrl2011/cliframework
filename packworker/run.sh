#!/bin/sh

# upload Discuz Android
#nohup ./runscript "php ./bin/upload.php 1 1" &

# run pack Discuz IOS
CUR_DIR=$(cd `dirname $0`; pwd)
kill -9 `ps aux | grep 'start.php -d' | grep $CUR_DIR |grep -v grep |awk '{print $2}'`
#kill -9 `ps aux | grep 'start.php -d' |grep -v grep |awk '{print $2}'`
nohup php $CUR_DIR/bin/start.php -d &

# upload Discuz IOS
kill -9 `ps aux | grep 'upload.php' | grep $CUR_DIR |grep -v grep | grep runscript |awk '{print $2}'`
kill -9 `ps aux | grep 'upload_online.php' | grep $CUR_DIR |grep -v grep | grep runscript |awk '{print $2}'`
#kill -9 `ps aux | grep 'upload.php' |grep -v grep | grep runscript |awk '{print $2}'`
nohup ./runscript "php $CUR_DIR/bin/upload.php 1 2" &
nohup ./runscript "php $CUR_DIR/bin/upload.php 2 2" &

nohup ./runscript "php $CUR_DIR/bin/upload_online.php 1 2" &
nohup ./runscript "php $CUR_DIR/bin/upload_online.php 2 2" &


#kill -9 `ps -ef | grep super | grep bin | awk '{print $2}'`;
#kill -9 `ps -ef | grep run | grep 'bash' | awk '{print $2}'`;
#kill -9 `ps -ef | grep start | grep -v grep | awk '{print $2}'`




