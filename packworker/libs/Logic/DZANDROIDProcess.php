<?php
class  DZANDROIDProcess implements BaseProcess {
	protected $package = null;
	protected $dataArray = null;
	protected $sourceDir = null;
	protected $packageDir = null;
	protected $appDir = null;
	
	public function downloadIMG(&$data){
		Timer::setStart(__FUNCTION__);
		$result = true;
		$this->dataArray = Utils::DataConverted($data);
               
		VseLog::TRACE('Package Data: '. json_encode($this->dataArray));
                $osType = Utils::getRunningSystemOSType();
		if($osType === 'LIN' || $osType === 'WIN') {
			if($this->dataArray['type'] == 'ios') {
				VseLog::TRACE('Failed. OS is '.$osType.". Task type is ".$this->dataArray['type']);
				return false;
			}

			VseLog::TRACE('OS is '.$osType.". Task type is ".$this->dataArray['type']);
				//打包安卓
			//$this->sourceDir = ROOT_PATH . '/' . Conf::$packScript['DZ_ANDROID']['sourcedir'];
            //$this->dataArray['outer_version'] = '1.0.0';
            //$this->dataArray['inner_version'] = '222';

			$outer_version = $this->dataArray['outer_version'];
			$inner_version = $this->dataArray['inner_version'];

			VseLog::TRACE("outer_veriosn: " . $outer_version . " --- inner_version: " . $inner_version);
			
			if(empty($outer_version) || empty($inner_version) || '0.0.0' === $outer_version || '0' === $inner_version) {
				$this->sourceDir = Conf::$sourcedir;
			} else {
				$this->sourceDir = Conf::$sourcedir . '/' . $outer_version . '/' . $inner_version;
			}
			
			VseLog::TRACE("sourcedir: " . $this->sourceDir);
			
			$this->packageDir = ROOT_PATH . '/' . Conf::$packScript['DZ_ANDROID']['pageagedir'];
			$this->appDir = ROOT_PATH . '/' . Conf::$packScript['DZ_ANDROID']['appdir'];
			$this->tmpDir = ROOT_PATH . '/' . Conf::$packScript['DZ_ANDROID']['tmpdir'];

			$this->package = new Package();
			//拷贝母包数据 /home/tangyy/packworker/./data/Android/DZ/source
			$result = $this->package->copyPackageCodes($this->sourceDir.'/Clan', $this->packageDir . '/' . $this->dataArray['id'], '/Clan/download_image/');
			if(!$result) {
				VseLog::TRACE('Copy package source code failed');
				return false;
			}

			$result = $this->package->beforePackage($this->dataArray, $this->packageDir . '/' . $this->dataArray['id'], '/Clan/download_image/');
			if(!$result) {
				VseLog::TRACE('Prepare work failed before packaging');
				return false;
			}
		}
		Timer::setEnd(__FUNCTION__);
		Timer::TRACE(__FUNCTION__, __FUNCTION__ . ' finished [result: %s ].', $result);
		return $result;
	}
	public function execShell(&$data){
		Timer::setStart(__FUNCTION__);
		$result = true;
		$result = $this->package->packageApp($this->packageDir . '/' . $this->dataArray['id']. "/Clan/Clan/");
		Timer::setEnd(__FUNCTION__);
		Timer::TRACE(__FUNCTION__, __FUNCTION__ . ' finished [result: %s ].', $result);
		return $result;
	}

	public function moveFiles(&$data){
		Timer::setStart(__FUNCTION__);
		$result = true;
		$result = $this->package->copyFiles($this->packageDir . '/' . $this->dataArray['id'] ,$this->tmpDir . '/' . $this->dataArray['id'], $this->appDir, $this->dataArray['id']);
		Timer::setEnd(__FUNCTION__);
		Timer::TRACE(__FUNCTION__, __FUNCTION__ . ' finished [result: %s ].', $result);
		return $result;
	}
}
?>
