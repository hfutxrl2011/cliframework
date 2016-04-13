#!/bin/bash
ps -fe|grep php |grep -v grep
if [ $? -ne 0 ]
then
echo "restart..."
cd /Users/youzu/work/packworker && sh run.sh > /dev/null
else
echo "runing..."
fi