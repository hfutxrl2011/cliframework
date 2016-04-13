<?php
require_once dirname(__FILE__) . '/BaseDb.class.php';

/* *****************************************************************************/
/**
* @Brief 数据仓库，所有数据均来自于此
*/
/* *****************************************************************************/
class DataEngine extends BaseDb
{	
	protected $_data = array(
		'yz_mob_taskqueue' => array(),
		'taskInfo' => array(),
		'taskid'=>null,
		'os'=>null,
		'app'=>null,
		'app_id'=>null,
		'bundleid'=>null,
		'app_key'=>null,
		'api_url'=>null,
	);

	
    public function getTaskqueueInfo() {
		Timer::setStart(__FUNCTION__);
		//$this->testTask();//hack
		//$this->preCheckTask(); to do
		//等待调度 超时的任务 执行超过次数的任务
		$sql = "SELECT taskid,os,app,times,mtime FROM yz_mob_taskqueue where "
		       ."( (status = 1 ) or "
			   ."(status < 5 and status >= 2 and mtime <" . (time()-Conf::$expireTask) ."  ) "
			   ." ) and "
			   ."times < ". Conf::$packOverTimes
			   ." and os = ". Conf::$packOS
			   ." order by priority DESC,ctime ASC limit 1";
        $ret = $this->query($sql);
		VseLog::trace('get data from db finished. [ sql: %s, ret: %s ].', $sql, json_encode($ret));
		if( isset($ret[0]) && !empty($ret[0]) ) {
			$this->_data['taskid'] = $ret[0]['taskid'];
			$this->_data['os'] = $ret[0]['os'];
			$this->_data['app'] = $ret[0]['app'];
            $this->_data['mtime'] = $ret[0]['mtime'];
		}else{
			$this->exitTask('1','get task empty');
		}
		$this->getTaskDetailInfo();
		$this->preCheckTask();
		$this->flowCheck();
		$this->lockTask();
		//$this->setMyappTaskFail();
		$this->getAppKey();
		$this->getShareSettingInfo();
		Timer::setEnd(__FUNCTION__);
		Timer::TRACE(__FUNCTION__, __FUNCTION__ . ' finished [ret  number: %s ].', count($ret));
		return $ret;
	}
	
	
	
	public function exitTask($status,$desc="",$exception=false){
		//VseLog::warning('fuck ,,' . json_encode(debug_backtrace()));
		VseLog::trace('task finished. [ status: %s, desc: %s, taskid: %s ].', $status, $desc,$this->_data['taskid']);
		if($exception){
			exit();
		}else{
			throw new Exception("task finished. [ status: $status, desc: $desc ]");
		}
	}
	
	//检查执行当前任务是否是一个应用的最新任务，否则放弃执行
	public function preCheckTask(){
		$sql = "SELECT count(q.taskid) as count FROM yz_mob_taskqueue as q left join dev_app_task as t "
			   ." on t.taskid=q.taskid and t.os=q.os  where "
		       ." (q.status >= 1 ) and "
			   ." (q.status <= 5 ) and "
			   ." (q.ctime > ".$this->_data['mtime']." ) and "
			   ." q.os = '". $this->_data['os']
			   ."' and t.app_id = '". $this->_data['app_id']
			   ."' and q.app = '". $this->_data['app']
			   ."' order by q.priority DESC,q.ctime ASC";
		$ret = $this->query($sql);
		if( isset($ret[0]['count']) && $ret[0]['count'] >= 1   ) {
			$this->updateStatus('0',false,'同时打包过多，系统拒绝');
		}
		/*
		$sql = "SELECT count(taskid) as count FROM yz_mob_taskqueue where "
		       ." (status >= 1 ) and "
			   ." (status < 5 ) and "
			   ." (ip = '".Conf::$packSvr .':'. ROOT_PATH ."' ) and "
			   ." os = ". Conf::$packOS
			   ." order by priority DESC,ctime ASC";
		$ret = $this->query($sql);
		if( isset($ret[0]['count']) && $ret[0]['count'] > 1   ) {
			$this->exitTask('1',Conf::$packSvr .':'. ROOT_PATH .' worker do too much task');
		}
		*/
	}
	//指定appid 在指定worker中运行
	public function flowCheck(){
		if(!empty(Conf::$flowControl[Conf::$packOS]['app_id']) 
			&& in_array($this->_data['app_id'],Conf::$flowControl[Conf::$packOS]['app_id'])
		){
			if(in_array(Conf::$packSvr,Conf::$flowControl[Conf::$packOS]['packsvr'])){
				return true;
			}else{
				$this->exitTask('2','this server can not do the task:'.Conf::$packSvr);
			}
		}
	}
	
	//获取拿到任务先锁定任务，锁定成功后才继续操作
	public function lockTask(){
		$this->startTransaction(false);
		$sql = "update yz_mob_taskqueue set status='2',times=times+1,ip='".Conf::$packSvr .':'. ROOT_PATH ."',mtime = '".time()
				."' where taskid='".$this->_data['taskid']
				."' and app='".$this->_data['app']
				."' and mtime='".$this->_data['mtime']
				."' and os='".$this->_data['os']."' limit 1";
		$this->query($sql);
		$ret = $this->getAffectRows(false);
		$this->updateTask(2,'locking...');
		$ret = $this->endTransaction(true);
		if(!$ret){
			$this->exitTask('1','lock task failed');
		}
	}
	//对于一个应用成功锁定某个任务之后，之前未完成的任务放弃
	public function setMyappTaskFail(){
		$sql = "update dev_app_task as t left join yz_mob_taskqueue as q "
				." on t.taskid=q.taskid and t.os=q.os "
				." set q.status='0' , t.status='0'"
				." where t.create_time < '".time()
				."' and q.status >='"."1"
				."' and q.status <='"."5"
				."' and q.app='".$this->_data['app']
				."' and q.taskid !='".$this->_data['taskid']
				."' and t.app_id='".$this->_data['app_id']
				."' and t.os='".$this->_data['os']
				."' and q.os='".$this->_data['os']
				."';";
		$this->query($sql);
		$ret = $this->getAffectRows(false);
		VseLog::trace("setMyappTaskFail finished. [ ret: $ret, sql:$sql ].");
	}
	
	public function testTask(){
		$sql = "update yz_mob_taskqueue set status='0'"
				." where taskid='"."13582272409488001622"
				."' and app='"."DZ"
				."' and os='2' limit 1";
		
		$this->query($sql);
	}
	
	//拿到下游需要的打包需要的数据，传递给打包脚本
	public function getTaskDetailInfo(){
		$sql = "select config,app_id, task_type from dev_app_task where taskid='".$this->_data['taskid']
				."' and os='".$this->_data['os']."' limit 1";

		$ret = $this->query($sql);
		if( isset($ret[0]) && !empty($ret[0]) ) {
			$this->_data['taskInfo'] = json_decode($ret[0]['config'],true);
			$this->_data['app_id'] = $ret[0]['app_id'];
			$this->_data['task_type'] = $ret[0]['task_type'];
		}else{
			$this->updateStatus(2,false,'get task config empty,sql='.$sql);
		}
	}
	
	public function getShareSettingInfo() {
		$sql = "select key_alias, store_password, key_password, key_store_content, share_plat from dev_share_setting where app_id='" . $this->_data['app_id']."'";
		//$sql = "select key_alias, store_password, key_password, key_store_content, share_plat from dev_share_setting where app_id='1086'";
		$ret = $this->query($sql);
		
		if(isset($ret[0]) && !empty($ret[0])) {
			$this->_data['key_alias'] = $ret[0]['key_alias'];
			$this->_data['store_password'] = $ret[0]['store_password'];
			$this->_data['key_password'] = $ret[0]['key_password'];
			$this->_data['key_store_content'] = $ret[0]['key_store_content'];
			$this->_data['share_plat'] = json_decode($ret[0]['share_plat'], true);
			if(!empty($this->_data['share_plat']))
			foreach($this->_data['share_plat'] as &$share_plat){
				if(isset($share_plat['flag']) && 0 == $share_plat['flag']){
					$share_plat['app_id'] = $share_plat['sec_key'] = '';
					if(isset($share_plat['redirect_url_sina'])){
						$share_plat['redirect_url_sina'] = '';
					}
				}
			}
		} else {
			//取default值
			$this->_data['key_alias'] = Conf::$defaultShareConfig['key_alias'];
			$this->_data['store_password'] = Conf::$defaultShareConfig['store_password'];
			$this->_data['key_password'] = Conf::$defaultShareConfig['key_password'];
			$this->_data['key_store_content'] = Conf::$defaultShareConfig['key_store_content'];
			$this->_data['share_plat'] = json_decode(Conf::$defaultShareConfig['share_plat'], true);
		}
		
		if($this->_data['key_alias'] === Conf::$defaultShareConfig['key_alias'] || 
			$this->_data['key_store_content'] === Conf::$defaultShareConfig['key_store_content']) {
			$this->_data['build_type'] = 'TEST';
		} else {
			$this->_data['build_type'] = 'RELEASE';
		}
		
		if($this->_data['build_type'] == 'TEST') {
			if(isset($this->_data['taskInfo']['choose_release']) && $this->_data['taskInfo']['choose_release'] == '1') {
				$this->_data['build_type'] = 'RELEASE';
			} else {
				$this->_data['build_type'] = 'TEST';
			}
		}
	}
	
	public function getAppKey(){
		$sql = "select app_key,api_url,real_api_url,bundleid from dev_app_info where app_id='".$this->_data['app_id']."'";
		$ret = $this->query($sql);
		if( isset($ret[0]) && !empty($ret[0]) ) {
			$this->_data['api_url'] = $ret[0]['api_url'];
			$this->_data['app_key'] = $ret[0]['app_key'];
			$this->_data['bundleid'] = $ret[0]['bundleid'];
			if(!empty($ret[0]['real_api_url'])){
				$this->_data['api_url'] = $ret[0]['real_api_url'];
			}
		}else{
			$this->updateStatus(2,false,'get app_key ,api_url empty,sql='.$sql);
		}
	}
	
	//更新状态数据，中间状态数据
	public function updateTaskQueueStatus($status,$desc=''){
		$sql = "update yz_mob_taskqueue set status='$status',extdata='$desc',mtime = '".time()
				."' where taskid='".$this->_data['taskid']
				."' and app='".$this->_data['app']
				."' and status!='".'0'//($status-1)
				."' and os='".$this->_data['os']."' limit 1";
		if(0 == $status){
			$sql = "update yz_mob_taskqueue set status='$status',extdata='$desc',mtime = '".time()
				."' where taskid='".$this->_data['taskid']
				."' and app='".$this->_data['app']
				."' and os='".$this->_data['os']."' limit 1";
		}
		$this->query($sql);
		$ret = $this->getAffectRows(false);
		return $ret;
	}
	
	//成功后更新原始任务表数据
	public function updateTask($status,$desc=''){
		$sql = "update dev_app_task set status='$status"
				."',`desc`='$desc' where taskid='".$this->_data['taskid']
				."' and status !='".'0'//($status-1)
				."' and os='".$this->_data['os']."' limit 1";
		if(0 == $status){
			$sql = "update dev_app_task set status='$status"
				."',`desc`='$desc' where taskid='".$this->_data['taskid']
				."' and os='".$this->_data['os']."' limit 1";
		}
		$this->query($sql);
		$ret = $this->getAffectRows(false);
		return $ret;
	}
	
	public function updateStatus($status,$result=true,$desc=null,$error_code=null){
		if(null == $desc){
			$desc = Conf::$status[$status];
		}
		if(!$result){
			$desc = Conf::$failedStatus[$status];
			//$desc .= ",status=$status";
			$status = 0;
		}
		if(null != $error_code){
			$desc = ",status=$status";
		}
		$this->startTransaction(false);
		$this->updateTask($status,$desc);
		$this->updateTaskQueueStatus($status,$desc);
		$ret = $this->endTransaction(true);
		if(!$ret){
			$this->updateStatus(0,false,'updateStatus failed,may be the app can not update task,stats:'.$status);
		}
		if(!$result){
			$this->exitTask($status,$desc);
		}
	}

	/* *****************************************************************************/
	/**
	* @Brief dumpDataFromDataBase 入口函数，将所有数据从数据库中dump出来
	*
	* @Returns NA
	*/
	/* *****************************************************************************/
	public function dumpDataFromDataBase()
	{
		Timer::setStart(__FUNCTION__);
		$this->_data['yz_mob_taskqueue'] = $this->getTaskqueueInfo();
		Timer::setEnd(__FUNCTION__);
		Timer::TRACE(__FUNCTION__, __FUNCTION__ . ' finished. data:'.json_encode($this->_data));
        return $this->_data;
	}
	
	/* *****************************************************************************/
	/**
	* @Brief getData 获取所有数据
	*
	* @Returns 所有有效数据
	*/
	/* *****************************************************************************/
	public function getData()
	{
		return $this->_data;
	}
	
	
	public function getQueneTasksMonitor($num=20){
		$sql = "select count(id) as count from yz_mob_taskqueue where status = 1";
		$ret = $this->query($sql);
		$result = false;
		if($ret[0]['count'] >= $num){
			$result = true;
		}
		return $result;
	}
	
	public function getFailedTimesMonitor($times=3){
		$sql = "select taskid from yz_mob_taskqueue where status >= 1 and status <=5 and times >= ".$times;
		$ret = $this->query($sql);
		$result = false;
		if(count($ret) > 1){
			$result = true;
			VseLog::trace('taskid info : %s .', json_encode($ret) ) ;
		}
		return $result;
	}
	
	public function getCostLongTimeMonitor($time=1800){
		$sql = "select taskid from yz_mob_taskqueue where status >= 2 and status <= 5 and mtime < ".(time()-$time);
		$ret = $this->query($sql);
		$result = false;
		if(count($ret) > 1){
			$result = true;
			VseLog::trace('taskid info : %s .', json_encode($ret) ) ;
		}
		return $result;
	}

}
?>
