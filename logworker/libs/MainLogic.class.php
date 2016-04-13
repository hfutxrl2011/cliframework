<?php
class MainLogic
{
	/**
	 * 
	 * 入口运行函数
	 * 如果出错，则抛出异常
	 */
	public function run()
	{
		VseLog::TRACE('STEP 1. START TO LOAD...');
		FactoryProcess::load();
		VseLog::TRACE('STEP 1. END TO LOAD!!!!!');
		VseLog::NOTICE('STEP N. ALL STEP END!!!!!!!!!!!');
	}
	
}

?>
