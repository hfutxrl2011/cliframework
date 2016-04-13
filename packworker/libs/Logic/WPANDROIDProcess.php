<?php
class  WPAndroidProcess implements BaseProcess {
	public function downloadIMG(&$data){
		
		Timer::setStart(__FUNCTION__);
		
		$result = true;
		$dataInfo = Utils::DataConverted($data);
		
		//建立目录
		$os = strtoupper($dataInfo['type']);
		VseLog::TRACE($dataInfo['app'].'_'.$os);
		$path = ROOT_PATH.'/'.Conf::$packScript[$dataInfo['app'].'_'.$os]['packagedir'].'/'.$dataInfo['id'];
		$result = FileWrapper::makeDir($path);
		
		//母包多版本支持
		$outer_version = $dataInfo['outer_version'];
		$inner_version = $dataInfo['inner_version'];

		VseLog::TRACE("outer_veriosn: " . $outer_version . " --- inner_version: " . $inner_version);
		if(empty($outer_version) || empty($inner_version) || '0.0.0' === $outer_version || '0' === $inner_version) {
			$sourceDir = Conf::$sourcedir_wp;
		} else {
			$sourceDir = Conf::$sourcedir_wp . '/' . $outer_version . '/' . $inner_version;
		}
		VseLog::TRACE("sourcedir: " . $sourceDir);
		
		//拷贝母包
		$result = FileWrapper::copyPackageCodes($sourceDir .'/UZWP', $path);
		
		//拷贝母包失败
		if(!$result) {
			VseLog::TRACE('Copy package source code failed');
			return false;
		}
		
		//下载远端图片
		$ext1 = Utils::getImgExt($dataInfo['pic']['icon']);
		$ext2 = Utils::getImgExt($dataInfo['pic']['recom']);
		$save1 = $path.'/UZWP/needs_files/icon'.$ext1;
		$save2 = $path.'/UZWP/needs_files/ic_init'.$ext2;
		
		$result = FileWrapper::tryDownloadFile($dataInfo['pic']['icon'],$save1);
		$result = FileWrapper::tryDownloadFile($dataInfo['pic']['recom'],$save2);
		if(!$result) {
			VseLog::TRACE('Download pictures failed');
			return false;
		}
		
		//压缩并替换图片
		$result = Utils::resizeImage($save1, $path.'/UZWP/needs_files/ic_launcher_48' . $ext1, 48, 48);
		$result = Utils::resizeImage($save1, $path.'/UZWP/needs_files/ic_launcher_72' . $ext1, 72, 72);
		$result = Utils::resizeImage($save1, $path.'/UZWP/needs_files/ic_launcher_96' . $ext1, 96, 96);
		$result = Utils::resizeImage($save1, $path.'/UZWP/needs_files/ic_launcher_144' . $ext1, 144, 144);
		if(!$result) {
			VseLog::TRACE('Resize pictures failed');
			return false;
		}
		
		Timer::setEnd(__FUNCTION__);
		Timer::TRACE(__FUNCTION__, __FUNCTION__ . ' finished [result: %s ].', $result);
		return $result;
	}
	
	public function execShell(&$data){
		Timer::setStart(__FUNCTION__);
		$result = true;
		
		$result = $this->replaceParams($data);
		
		if(!$result) return false;
		
		$dataInfo = Utils::DataConverted($data);
		$os = strtoupper($dataInfo['type']);
		$path = ROOT_PATH.'/'.Conf::$packScript[$dataInfo['app'].'_'.$os]['packagedir'].'/'.$dataInfo['id'];
		
		$currentDir = getcwd();
		chdir($path.'/UZWP');
		$cmd = 'gradle clean';
		$result = Utils::shellExec2($cmd);
		$cmd = 'gradle assembleRelease';
		$result = Utils::shellExec2($cmd);
		
		chdir($currentDir);
		
		if(!$result) {
			VseLog::TRACE('Run gradle script error, packaging failed');
			return false;
		}
		
		Timer::setEnd(__FUNCTION__);
		Timer::TRACE(__FUNCTION__, __FUNCTION__ . ' finished [result: %s ].', $result);
		return $result;
	}
	
	public function replaceParams($data){
		$result = true;
		
		$replace = array();
		$dataInfo = Utils::DataConverted($data);
		//nav_color
		$replace['nav_color'] = $dataInfo['color_rgb'];
		$replace['app_name'] = $dataInfo['appname'];
		$replace['package_name'] = $dataInfo['package_name'];
		$replace['version_name'] = $dataInfo['version_name'];
		$replace['api_url'] = $dataInfo['api_url'];
		$replace['store_pwd'] = $dataInfo['store_password'];
		$replace['key_alias'] = $dataInfo['key_alias'];
		$replace['key_pwd'] = $dataInfo['key_password'];
		$replace['channel_name'] = $dataInfo['channel'];
		$replace['qq_app_id'] = $dataInfo['share_plat'][1]['app_id'];
		$replace['app_id'] = $dataInfo['app_id'];
		$replace['app_key'] = $dataInfo['app_key'];
		$replace['use_wechat'] = $dataInfo['share_plat'][0]['flag'];//微信
		$replace['use_qq'] = $dataInfo['share_plat'][1]['flag'];//QQ
		$replace['use_sina'] = $dataInfo['share_plat'][2]['flag'];//新浪微博
		$replace['task_type'] = $dataInfo['task_type'];
		
		$search['nav_color'] = '#FFFFFF';
		$search['app_name'] = 'bigApp';
		$search['package_name'] = 'com.youzu.wp.bigword';
		$search['version_name'] = '1.0.1_18023';
		$search['api_url'] = 'http://192.168.180.23:8080/luyiword/wordpress/';
		$search['store_pwd'] = '1422@youzu';
		$search['key_alias'] = 'youyu.keystore';
		$search['key_pwd'] = 'xiaoshun@youzu';
		$search['channel_name'] = 'xiaomi';
		$search['qq_app_id'] = '1104849854';
		$search['app_id'] = '1067';
		$search['app_key'] = '8a67e0f266edd93b610a9348415968fb';
		$search['use_wechat'] = 'use_wechat_0';//微信
		$search['use_qq'] = 'use_qq_0';//QQ
		$search['use_sina'] = 'use_sina_0';//新浪微博
		$search['task_type'] = 'debug_flag_0';
		
		$os = strtoupper($dataInfo['type']);
		$path = ROOT_PATH.'/'.Conf::$packScript[$dataInfo['app'].'_'.$os]['packagedir'].'/'.$dataInfo['id'];
		$targetFile = $path . '/UZWP/needs_files/params.xml';
		
		foreach($replace as $key => $value) {
			$old = str_replace(',', '\,', $search[$key]);
			$new = str_replace(',', '\,', $replace[$key]);
			$old = str_replace("'", "\'", $search[$key]);
			$new = str_replace("'", "\'", $replace[$key]);

			$cmd = "sed -i ". "'s," . $old . "," . $new . ",g' " . $targetFile;
		
			$result = Utils::shellExec($cmd);
			
			if(!$result) {
				VseLog::TRACE($key. ' -- params replace failed. ' . $cmd);
				return false;
			}
		}
		
		$replace = array();
		$search = array();
		
		$targetFile = $path . '/UZWP/needs_files/ShareSDK.xml';
		//$replace['use_wechat'] = $dataInfo['share_plat'][0]['flag'];//微信
		$replace['wechat_appid'] = $dataInfo['share_plat'][0]['app_id'];//微信
		$replace['wechat_secret'] = $dataInfo['share_plat'][0]['sec_key'];//微信
		//$replace['use_qq'] = $dataInfo['share_plat'][1]['flag'];//QQ
		$replace['qq_appid'] = $dataInfo['share_plat'][1]['app_id'];//QQ
		$replace['qq_secret'] = $dataInfo['share_plat'][1]['sec_key'];//QQ
		//$replace['use_sina'] = $dataInfo['share_plat'][2]['flag'];//新浪微博
		$replace['sina_appid'] = $dataInfo['share_plat'][2]['app_id'];//新浪微博
		$replace['sina_secret'] = $dataInfo['share_plat'][2]['sec_key'];//新浪微博
		$replace['sina_redirect_url'] = $dataInfo['share_plat'][2]['redirect_url_sina'];//新浪微博
		

		$search['wechat_appid'] = 'wxf9a41e46b0ab3dcc';//微信
		$search['wechat_secret'] = '0374a10696524380bf93bd90c7748218';//微信
		$search['use_qq'] = $dataInfo['share_plat'][1]['flag'];//QQ
		$search['qq_appid'] = '1104849854';//QQ
		$search['qq_secret'] = 'h5vcW1xNPt6qxBdK';//QQ
		$search['use_sina'] = $dataInfo['share_plat'][2]['flag'];//新浪微博
		$search['sina_appid'] = '1361202962';//新浪微博
		$search['sina_secret'] = '835bc10f06d7b6feda3a3b4bd3087473';//新浪微博
		$search['sina_redirect_url'] = 'https://api.weibo.com/oauth2/default.html';//新浪微博
		
		foreach($replace as $key => $value) {
			$old = str_replace(',', '\,', $search[$key]);
			$new = str_replace(',', '\,', $replace[$key]);
			$old = str_replace("'", "\'", $search[$key]);
			$new = str_replace("'", "\'", $replace[$key]);

			$cmd = "sed -i ". "'s," . $old . "," . $new . ",g' " . $targetFile;
		
			$result = Utils::shellExec($cmd);
			
			if(!$result) {
				VseLog::TRACE($key. ' -- params replace failed. ' . $cmd);
				return false;
			}
		}
		
		//写入keystore文件
		$targetFile = $path . '/UZWP/needs_files/youyu.keystore';
		$result = file_put_contents($targetFile, base64_decode($data['key_store_content']));
	
		if(!$result) {
			VseLog::TRACE('Keystore file replace failed. ');
			return false;
		}

		return $result;
	}
	
	public function moveFiles(&$data){
		Timer::setStart(__FUNCTION__);
		$result = true;
		
		$dataInfo = Utils::DataConverted($data);
		
		$src = ROOT_PATH . '/' . Conf::$packScript['WP_ANDROID']['packagedir']. '/' .$dataInfo['id'];
		$des = ROOT_PATH . '/' . Conf::$packScript['WP_ANDROID']['appdir'];
		$tmp = ROOT_PATH . '/' . Conf::$packScript['WP_ANDROID']['tmpdir'] . '/' .$dataInfo['id'];
		
		//文件移动
		$currentDir = getcwd();
		if(file_exists($tmp)) {
			$cmd = 'rm -rf '. $tmp;
			$result = Utils::shellExec($cmd);
			if(!$result) {
				VseLog::TRACE('Delete old tmp direcotry failed before moving apk, tar.gz files');
				return false;
			}
		}

		$result = mkdir($tmp, 0777, true);
		if(!$result) {
			VseLog::TRACE('Tmp direcotry create failed before moving apk, tar.gz files');
			return false;
		}
		
		chdir($src);
		$cmd = "tar -zcvf " . $dataInfo['id'] . ".tar.gz " . $src;
		Utils::shellExec($cmd, null, null, $currentDir);
		
		//if(!$status) {
		//	VseLog::TRACE('Gzip the source codes failed before moving apk, tar.gz files');
		//	return false;
		//}

	    if(file_exists($src . "/UZWP/outputs/bigApp_wp.apk")) {		
			VseLog::TRACE('APK file built success');
			
			$cmd1 =  "cp -a ". $src . "/UZWP/outputs/bigApp_wp.apk " . $tmp . "/" . $dataInfo['id'] . ".apk";
			$status1 = Utils::shellExec($cmd1);
			$cmd2 = "cp -r ". $src . "/" . $dataInfo['id'].".tar.gz " . $tmp;
			$status2 = Utils::shellExec($cmd2);
			$cmd3 = "mv ". $tmp . " " . $des;
			$status3 = Utils::shellExec($cmd3);

			if(!$status1 || !$status2 || !$status3) {
				VseLog::TRACE("Move apk, tar.gz files failed");
				return false;
			}
		} else {
			VseLog::TRACE("Apk file didn't created");
			return false;
		}
		
		Timer::setEnd(__FUNCTION__);
		Timer::TRACE(__FUNCTION__, __FUNCTION__ . ' finished [result: %s ].', $result);
		
		return $result;
	}
	
}
?>
