<?php
class  LogProcess implements BaseProcess {
	private $_type = 1;
	private $data = array();
	
	public function run(){
		$this->preProcess()
			->runProcess()
			->doneProcess();
	}
	
	public function preProcess(){
		$this->data = Conf::$logRule[$this->_type];
		VseLog::TRACE(__FUNCTION__ .' done');
		return $this;
	}
	
	public function runProcess(){
		foreach($this->data as $task){
			VseLog::TRACE(__FUNCTION__ .' start to do one task:'.json_encode($task));
			if(file_exists($task['path'])){
				if(is_array($task['filename'])){
					foreach($task['filename'] as $file){
						$filePath = $task['path'].$file;
						if(file_exists($filePath)){
							//切割
							Utils::cutLog($filePath,$task['cuttime']);
							//备份
							Utils::backupLog($filePath,$task['backuptime']);
							//删除
							Utils::removeLog($filePath,$task['rmtime']);
						}else{
							VseLog::TRACE(__FUNCTION__ .' file not exist:'.$filePath);
						}
					}
				}else{
					VseLog::TRACE(__FUNCTION__ .' to do...');
				}
			}else{
				VseLog::TRACE(__FUNCTION__ .' file not exist:'.$task['path']);
			}
		}
		VseLog::TRACE(__FUNCTION__ .' done');
		return $this;
	}
	
	public function doneProcess(){
		
		VseLog::TRACE(__FUNCTION__ .' done. the task is:'.json_encode($this->data));
		return $this;
	}
	
}
?>
