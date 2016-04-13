#/bin/bash

# run Android packworker
CUR_DIR=$(cd `dirname $0`; pwd)
#stop packworker

kill -9 `ps aux | grep "${CUR_DIR}/supervise bin" | grep -v grep |awk '{print $2}'`
kill -9 `ps aux | grep '/bin/bash ./run' |grep -v grep |awk '{print $2}'`
kill -9 `ps aux | grep 'start.php -d' | grep -v grep |awk '{print $2}'`
kill -9 `ps aux | grep "runscript php ${CUR_DIR}/bin/upload.php" | grep -v grep |awk '{print $2}'`


#start packworker
nohup $CUR_DIR/supervise bin &

cd $CUR_DIR/tool
make

yes | cp runscript ../

cd $CUR_DIR

nohup ./runscript "php $CUR_DIR/bin/upload.php 1 1" &
nohup ./runscript "php $CUR_DIR/bin/upload.php 2 1" &
