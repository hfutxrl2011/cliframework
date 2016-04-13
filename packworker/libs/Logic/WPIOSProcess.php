<?php
class  WPIOSProcess implements BaseProcess {
	public $sourceDir = null;
	
	public function __construct(){
		
	}
	
	public function downloadIMG(&$data){
		Timer::setStart(__FUNCTION__);
		if(isset($data['key_store_content'])){
			unset($data['key_store_content']);
		}
		$result = true;
		//建立目录
		$os = Conf::$osMap[$data['os']];
		$path = ROOT_PATH.'/'.Conf::$packScript[$data['app'].'_'.$os]['packagedir'].'/'.$data['taskid'];
		if(!file_exists($path)){
			$result = FileWrapper::makeDir($path);
		}
		
		//下载图片
		$ext1 = Utils::getImgExt($data['taskInfo']['icon_image']);
		$ext2 = Utils::getImgExt($data['taskInfo']['startup_image']);
		$save1 = $path.'/icon_image'.$ext1;
		$save2 = $path.'/startup_image'.$ext2;
		$result = FileWrapper::downloadFile($data['taskInfo']['icon_image'],$save1);
		$result = FileWrapper::downloadFile($data['taskInfo']['startup_image'],$save2);
		
		$data['icon_image'] = $save1;
		$data['startup_image'] = $save2;
		Timer::setEnd(__FUNCTION__);
		Timer::TRACE(__FUNCTION__, __FUNCTION__ . ' finished [result: %s ].', $result);
		return $result;
	}
	public function execShell(&$data){
		Timer::setStart(__FUNCTION__);
		$result = true;
		$this->getSourceVersionPath($data);
		$sourceDir = $this->sourceDir;
		if(WORKER_ENV == 'Prod'){
			$cmd = 'cd '.$sourceDir.' && chmod 777 pack_online.sh && chmod 777 resign_ipa.sh && /bin/bash ./pack_online.sh';
		}else{
			$cmd = 'cd '.$sourceDir.' && /bin/bash ./pack_online.sh';
		}
		$os = Conf::$osMap[$data['os']];
		$params = Conf::$packScript[$data['app'].'_'.$os]['params'];
		$newParams = $this->getParams($data,$params);
		$log = ROOT_PATH . '/log/script.log';
		$result = Utils::shellExec2($cmd,$newParams,$log);
		Timer::setEnd(__FUNCTION__);
		Timer::TRACE(__FUNCTION__, __FUNCTION__ . ' finished [result: %s ].', $result);
		return $result;
	}
	
	/*
	"app_key"=>"appKey",
	"app_channel"=>"appChanel",
	"app_name"=>"博客002",
	"package_name"=>"com.youzu.test",
	"version_name"=>"1.0.0",//客户端版本号
	//"inner_version"=>"100",//内部版本号
	"nav_color"=>"red",//#198CE4
	"api_url"=>"http://www.youzu.com",
	//"svn_version"=>"3474",//svn 版本号
	"icon_image"=>"$(pwd)/icon.png",
	"startup_image"=>"$(pwd)/launch.png",
	"ouput_dir"=>"$(date +%Y-%m-%d_%H_%M)",
	"shareKey_wechat"=>"wxc38fe19026b7591a",//14 shareKey_wechat="${14}"
	"shareSecret_wechat"=>"f9e6050f7ab583e490d3745e1d2c607b",//15 shareSecret_wechat="${15}"
	"sharekey_qq"=>"1104574417",//16 sharekey_qq="${16}"
	"shareSecret_qq"=>"n00QoTcEUBPWqtHL",//17 shareSecret_qq="${17}"
	"sharekey_sina"=>"2257958082",//11 sharekey_sina="${11}"
	"shareSecret_sina"=>"61ec8c63481817f3c122af6c9d2596ff",//12 shareSecret_sina="${12}"
	"shareRedirecturi_sina"=>"http://www.3body.com/",//13 shareRedirecturi_sina="${13}"
	*/
	public function getParams($data,$params){
		
		$newParams = array();
		$newParams['app_key'] = $data['app_key'];
		$newParams['app_channel'] = $data['taskInfo']['channel_name'];
		$newParams['app_name'] = $data['taskInfo']['app_name'];
		$newParams['package_name'] = $data['taskInfo']['package_name'];
		$newParams['version_name'] = $data['taskInfo']['version_name'];
		$newParams['nav_color'] = $data['taskInfo']['nav_color'];
		//$newParams['inner_version'] = $params['inner_version'];
		$newParams['api_url'] = $data['api_url'];
		//$newParams['svn_version'] = $params['svn_version'];
		$newParams['icon_image'] = $data['icon_image'];
		$newParams['startup_image'] = $data['startup_image'];
		$newParams['ouput_dir'] = $data['taskid'];
		$newParams['shareKey_wechat'] = $data['share_plat'][0]['app_id'];//$14
		$newParams['shareSecret_wechat'] = $data['share_plat'][0]['sec_key'];//$15
		$newParams['sharekey_qq'] = $data['share_plat'][1]['app_id'];//$16
		$newParams['shareSecret_qq'] = $data['share_plat'][1]['sec_key'];//$17
		$newParams['sharekey_sina'] = $data['share_plat'][2]['app_id'];//$11
		$newParams['shareSecret_sina'] = $data['share_plat'][2]['sec_key'];//$12
		$newParams['shareRedirecturi_sina'] = $data['share_plat'][2]['redirect_url_sina'];//$13
		if(is_array($params)){
			foreach($params as $key => $value){
				//if(isset($newParams[$key]) && !empty($newParams[$key]) ){
				if(isset($newParams[$key]) ){
					$params[$key] = $newParams[$key];
				}else{
					VseLog::trace(">>>>>> $key unset");
					throw new Exception("cmd params error,$key unset in " . __FUNCTION__  );
				}
			}
		}
		return $params;
	}
	public function moveFiles(&$data){
		Timer::setStart(__FUNCTION__);
		$result = true;
		//tar plist ipa文件拷贝 
		
		//文件移动
		$os = Conf::$osMap[$data['os']];
		$source_path = ROOT_PATH.'/'.Conf::$packScript[$data['app'].'_'.$os]['packagedir'].'/'.$data['taskid'];
		$des_path = ROOT_PATH.'/'.Conf::$packScript[$data['app'].'_'.$os]['appdir'];
		
		$result = FileWrapper::makeDir($source_path);
		
		//plist文件
		$sourceDir = ROOT_PATH.'/'.Conf::$packScript[$data['app'].'_'.$os]['sourcedir'];
		$IOSPACKAGE = $this->sourceDir;
		$plist = $sourceDir.'/clan.plist';
		
		$content = @file_get_contents($plist);
		$data['taskInfo']['icon_image'] = str_replace("&",'&amp;',$data['taskInfo']['icon_image']);
		$content = str_replace("logoUrl",$data['taskInfo']['icon_image'],$content);
		$content = str_replace("ipaUrl",Conf::$fileServer4Os.'IOS/'.date("Ym").'/'.$data['taskid'].'.ipa',$content);
		$content = str_replace("appName",$data['taskInfo']['app_name'],$content);
		@file_put_contents($source_path.'/'.$data['taskid'].'.plist',$content);
	    
		//tar 文件 ipa文件
		$cmd = '/bin/bash '.$sourceDir.'/mvFiles.sh';
		$params = array(
						 'des'=>$source_path,
		                 'taskid'=>$data['taskid'],
						 'src'=>$IOSPACKAGE.'build/'.$data['taskid'],
						 'app_path'=>$des_path
				 );
		$log = ROOT_PATH . '/log/script.log';
		$result = Utils::shellExec($cmd,$params,$log);
		
		$result = true;//hack to do
		Timer::setEnd(__FUNCTION__);
		Timer::TRACE(__FUNCTION__, __FUNCTION__ . ' finished [result: %s ].', $result);
		
		return $result;
	}
	
	public function getSourceVersionPath(&$data){
		$os = Conf::$osMap[$data['os']];
		$this->sourceDir = Conf::$packScript[$data['app'].'_'.$os]['scriptdir'];
		if(isset($data['taskInfo']['inner_version']) && isset($data['taskInfo']['outer_version'])){
			$tmpPath = rtrim($this->sourceDir,'/')  . $data['taskInfo']['outer_version'] . '_' . $data['taskInfo']['inner_version'];
			if(file_exists($tmpPath)){
				$this->sourceDir = rtrim($this->sourceDir,'/') . $data['taskInfo']['outer_version'] . '_' . $data['taskInfo']['inner_version'].'/';
				VseLog::trace(">>>>>> $tmpPath exist");
			}else{
				VseLog::trace(">>>>>> $tmpPath not exist");
			}
		}
		VseLog::trace(">>>>>> source dir".$this->sourceDir);
	}
}
?>
