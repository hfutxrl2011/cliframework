for i in `grep "*UTF8DIFF*" -r ./ | grep -v ".svn" | awk -F":" '{print $1}' | uniq`; 
do 
	sed "s/'GBK'\/\*UTF8DIFF\*\//'UTF-8'\/\*UTF8DIFF\*\//g" -i $i;
	sed "s/'GB2312'\/\*UTF8DIFF\*\//'UTF-8'\/\*UTF8DIFF\*\//g" -i $i;
	sed "s/'GB18030'\/\*UTF8DIFF\*\//'UTF-8'\/\*UTF8DIFF\*\//g" -i $i; 
done
