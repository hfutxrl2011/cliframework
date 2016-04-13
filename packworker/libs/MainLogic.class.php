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
		Utils::initEnv();
		
		VseLog::TRACE('STEP 0. START TO PACK IOS ONLINE...');
		$this->packIOS();
		VseLog::TRACE('STEP 0. END TO PACK IOS ONLINE...');
		
		//从数据库读取取一条符合条件的任务，并将改任务锁定
		VseLog::TRACE('STEP 1. START TO DUMP DATA FROM DATABASE...');
		$dataEngine = new DataEngine();
		$data = $dataEngine->dumpDataFromDataBase();
		
		VseLog::TRACE('STEP 1. END TO DUMP DATA FROM DATABASE!!!!!');

		$result = true;
		$myProcess = $data['app'].Conf::$osMap[$data['os']]."Process";
		
		//新打包方式
		$newDzIOS = true;
		if($newDzIOS && ('DZIOSProcess' == $myProcess) ){
			$myProcess = 'NewDZIOSProcess';
		}
		if(!class_exists("$myProcess")){
			$dataEngine->updateStatus(1,true,'class not exists:'.$myProcess);
			$dataEngine->exitTask(2,'class not exists:'.$myProcess);
		}
		$packWork = new $myProcess();
		//打包前准备 图片裁剪，数据验证，项目文件信息替换
		VseLog::TRACE('STEP 2. START TO DO PRE WORKER...');
		$result = $packWork->downloadIMG($data);
		$dataEngine->updateStatus(3,$result);
		VseLog::TRACE('STEP 2. END TO DO PRE WORKER!!!!!');
			
		//执行打包程序 进行打包操作 执行编译脚本
		VseLog::TRACE('STEP 3. START TO DO WORKER...');
		$result = $packWork->execShell($data);
		$dataEngine->updateStatus(4,$result);
		VseLog::TRACE('STEP 3. END TO DO WORKER!!!');
			
		//打包完成后，数据包的远程拷贝
		VseLog::TRACE('STEP 4. START TO COPY FILES...');
		$result = $packWork->moveFiles($data);
		$dataEngine->updateStatus(5,$result);
		VseLog::TRACE('STEP 4. END TO COPY FILES!!!');
			
		unset($data);
		unset($dataEngine);
		VseLog::NOTICE('STEP N. ALL STEP END!!!!!!!!!!!');
	}
	
	public function packIOS(){
		$ret = (Conf::$packTask & Conf::$packTaskConf['IOS_Online']) == Conf::$packTaskConf['IOS_Online'] ;
		if(2 == Conf::$packOS && $ret){
			VseLog::TRACE('IOS STEP 1 start');
			$IOSEngine = new IOSEngine();
			$data = $IOSEngine->dumpDataFromDataBase();
			
			//获取数据为空
			if( false === $data || empty($data['IOS_taskqueue']) || empty($data['taskInfo']) ) {
				return;
			}
			
			VseLog::TRACE('IOS STEP 1 end');

			$packWork = new OnlineIOSProcess();
			$packWork->doProcess($data,$IOSEngine);
			unset($data);
		    unset($IOSEngine);
			VseLog::NOTICE('packIOS done!!!');
		}
	}
	
}

?>
