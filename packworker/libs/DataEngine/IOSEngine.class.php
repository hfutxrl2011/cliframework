<?php
require_once dirname(__FILE__) . '/BaseDb.class.php';

/* *****************************************************************************/
/**
* @Brief 数据仓库，IOS上线数据
*/
/* *****************************************************************************/
class IOSEngine extends BaseDb
{	
	protected $_data = array(
		'IOS_taskqueue' => array(),
		'taskInfo' => array(),
		'taskid'=>null,
		'bundleid'=>null,
		'os'=>'2',
		'app'=>'DZ',
		'app_id'=>null,
		'app_key'=>null,
		'api_url'=>null,
	);

    public function getTaskqueueInfo() {
		Timer::setStart(__FUNCTION__);
		$sql = "SELECT * FROM dev_app_ios where "
		       ." status = 1 "
			   ." order by ctime ASC limit 1";
        $ret = $this->query($sql);
		VseLog::trace('get data from db finished. [ sql: %s, ret: %s ].', $sql, json_encode($ret));
		if( isset($ret[0]) && !empty($ret[0]) ) {
			$this->_data['IOS_taskqueue'] = $ret[0];
			$this->_data['taskid'] = $ret[0]['taskid'];
		}else{
			return false;
		}
		$this->lockTask();
		$this->getTaskDetailInfo();
		$this->getQueueInfo();
		Timer::setEnd(__FUNCTION__);
		Timer::TRACE(__FUNCTION__, __FUNCTION__ . ' finished [ret  number: %s ].', count($ret));
		return $ret;
	}
	
	public function exitTask($status,$desc="",$exception=false){
		VseLog::trace('task finished. [ status: %s, desc: %s, taskid: %s ].', $status, $desc,$this->_data['taskid']);
		if($exception){
			exit();
		}else{
			throw new Exception("task finished. [ status: $status, desc: $desc ]");
		}
	}

	
	//获取拿到任务先锁定任务，锁定成功后才继续操作
	public function lockTask(){
		$sql = "update dev_app_ios set status='2',mtime='".date("Y-m-d H:i:s")."' where id = '" 
		. $this->_data['IOS_taskqueue']['id']
		."' and mtime='".$this->_data['IOS_taskqueue']['mtime']."' limit 1";
		$this->query($sql);
		$ret = $this->getAffectRows(false);
		if(!$ret){
			$this->exitTask('1','lock task failed');
		}
	}
	
	//拿到下游需要的打包需要的数据，传递给打包脚本
	public function getTaskDetailInfo(){
		$sql = "select config,app_id from dev_app_task where taskid='".$this->_data['taskid']
				."' and os='2' limit 1";

		$ret = $this->query($sql);
		if( isset($ret[0]) && !empty($ret[0]) ) {
			$this->_data['taskInfo'] = json_decode($ret[0]['config'],true);
			$this->_data['app_id'] = $ret[0]['app_id'];
		}else{
			$this->updateStatus(2,false,'get task config empty,sql='.$sql);
		}
	}
	
	public function getQueueInfo(){
		$sql = "select app from yz_mob_taskqueue where taskid='".$this->_data['taskid']
				."' and os='2' limit 1";

		$ret = $this->query($sql);
		if( isset($ret[0]) && !empty($ret[0]) ) {
			$this->_data['app'] = $ret[0]['app'];
		}else{
			$this->updateStatus(2,false,'get task queue empty,sql='.$sql);
		}
	}
	
	public function updateStatus($status,$result=true){
		if(!$result){
			$status = 0;
		}
		$sql = "update dev_app_ios set status='$status"
				."' where id='". $this->_data['IOS_taskqueue']['id']
				."'  limit 1";
		$this->query($sql);
		$ret = $this->getAffectRows(false);
		return $ret;
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
		$this->getTaskqueueInfo();
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

}
?>
