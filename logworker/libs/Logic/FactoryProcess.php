<?php
class FactoryProcess {
	public static function load(){
		$list = Conf::$taskList;
		foreach($list as $type){
			if(0 == $type){
				self::runAll();
			}else{
				self::runProcess($type);
			}
		}
		VseLog::TRACE(__FUNCTION__ .' done');
	}
	
	public static function runAll(){
		$rule = Conf::$logRule;
		foreach($rule as $type => $tasks){
			self::runProcess($type);
		}
		VseLog::TRACE(__FUNCTION__ .' done');
	}
	
	public static function runProcess($type){
		if(isset(Conf::$logType[$type])){
			$myProcess = Conf::$logType[$type];
			if(class_exists($myProcess)){
				$process = new $myProcess();
				$result = $process->run();
				VseLog::TRACE(__FUNCTION__ .' my process run:'.$myProcess);
			}else{
				VseLog::TRACE(__FUNCTION__ .' process not exist:'.$myProcess);
			}
		}else{
			VseLog::TRACE(__FUNCTION__ .' task is not set:'.$type);
		}
		VseLog::TRACE(__FUNCTION__ .' done');
	}
}
?>
