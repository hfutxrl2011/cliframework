<?php
require_once("start.php");

$subject = "打包中心异常监控";
$content = "</br>打包中心异常，请相关人员关注。</br></br>".PHP_EOL;
$to = array("xierl@youzu.com","wuj@uuzu.com","tangyy@uuzu.com");

$dataEngine = new DataEngine();
if($dataEngine->getQueneTasksMonitor()){
    $content .= "</br>当打包服务器任务积压数量超过设定值（20个）时自动邮件提醒检查.";
	$ret = Utils::sendEmail($subject,$content,$to);
	echo "done mon 1".PHP_EOL;
	var_dump($ret);exit;
}

if($dataEngine->getFailedTimesMonitor()){
    $content .= "</br>当打包服务器某一任务打包失败重试次数超过设定值（3次）时，自动邮件提醒检查.";
	$ret = Utils::sendEmail($subject,$content,$to);
	echo "done mon 2".PHP_EOL;
	var_dump($ret);exit;
}

if($dataEngine->getCostLongTimeMonitor()){
	 $content .= "</br>当打包服务器某一任务状态为未关闭情况时，最后更新时间超过设定值（30分钟）时，自动邮件提醒检查.";
	$ret = Utils::sendEmail($subject,$content,$to);
	echo "done mon 3".PHP_EOL;
	var_dump($ret);exit;
}

echo "nothing done!!!".PHP_EOL;exit;

?>
