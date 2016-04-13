#!/bin/bash
process='php /Users/youzu/work/packworker/bin/start.php -d';
pid=`ps aux | grep '$process' |grep -v grep |awk '{print $2}'`;
if [ $? -ne 0 ];
then
	echo "restart...";
	cd /Users/youzu/work/packworker && sh run.sh > /dev/null;
else
	echo "runing...";
fi