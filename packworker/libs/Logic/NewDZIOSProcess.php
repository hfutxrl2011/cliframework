<?php
class  NewDZIOSProcess implements BaseProcess {
	
	public $sourceDir;
	
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
		//$ext1 = $ext2 = '.png';
		$save1 = $path.'/icon_image'.$ext1;
		$save2 = $path.'/startup_image'.$ext2;
		$result = FileWrapper::tryDownloadFile($data['taskInfo']['icon_image'],$save1);
		$result = FileWrapper::tryDownloadFile($data['taskInfo']['startup_image'],$save2);
		
		//对于png图片强制转码一次，防止IOS打包编译失败
		
		if($ext1 == '.png'){
			$save11 = $path.'/icon_image1'.$ext1;
			if(!Utils::thumbIMG("1024","1024",$save11,$save1)){
				VseLog::TRACE('thumb failed,icon_image:'.$save1);
			}else{
				$save1 = $save11;
				VseLog::TRACE('thumb succ,icon_image:'.$save11);
			}
		}
		if($ext2 == '.png' ){
			$save22 = $path.'/startup_image1'.$ext2;
			if(!Utils::thumbIMG("1242","2208",$save22,$save2)){
				VseLog::TRACE('thumb failed,startup_image:'.$save2);
			}else{
				$save2 = $save22;
				VseLog::TRACE('thumb succ,startup_image:'.$save22);
			}
		}
		$data['icon_image'] = $save1;
		$data['startup_image'] = $save2;
		Timer::setEnd(__FUNCTION__);
		Timer::TRACE(__FUNCTION__, __FUNCTION__ . ' finished [result: %s ].', $result);
		return $result;
	}


	public function execShell(&$data){
		$packageDir = '/Users/youzu/Desktop/IOSPACKAGE/DZ_PACKAGE/';
		//$os = Conf::$osMap[$data['os']];
		//$packageDir = ROOT_PATH.'/'.Conf::$packScript[$data['app'].'_'.$os]['sourcedir'];
		$params = $this->getNewParams($data);
		if(WORKER_ENV == 'Prod'){
			$cmd = 'cd '.$packageDir.' && chmod 777 online.sh && ./online.sh';
		}else{
			$cmd = 'cd '.$packageDir.' && chmod 777 offline.sh && ./offline.sh';
		}
		$result = Utils::shellExec2($cmd,$params);
		return $result;
	}
	
	public function moveFiles(&$data){
		Timer::setStart(__FUNCTION__);
		$result = true;
		//tar plist ipa文件拷贝 
		
		//文件移动
		$os = Conf::$osMap[$data['os']];
		$source_path = ROOT_PATH.'/'.Conf::$packScript[$data['app'].'_'.$os]['packagedir'].'/'.$data['taskid'];
		$des_path = ROOT_PATH.'/'.Conf::$packScript[$data['app'].'_'.$os]['appdir'];
		
		if(!file_exists($source_path)){
			$result = FileWrapper::makeDir($source_path);
		}
		//plist文件
		$sourceDir = ROOT_PATH.'/'.Conf::$packScript[$data['app'].'_'.$os]['sourcedir'];
		//$IOSPACKAGE = Conf::$packScript[$data['app'].'_'.$os]['IOSPACKAGE'];
		$IOSPACKAGE = '/Users/youzu/Desktop/IOSPACKAGE/DZ_PACKAGE/';
		$plist = $sourceDir.'/clan.plist';
		
		$content = @file_get_contents($plist);
		$data['taskInfo']['icon_image'] = str_replace("&",'&amp;',$data['taskInfo']['icon_image']);
		$content = str_replace("logoUrl",$data['taskInfo']['icon_image'],$content);
		$content = str_replace("ipaUrl",Conf::$fileServer4Os.'IOS/'.date("Ym").'/'.$data['taskid'].'.ipa',$content);
		$content = str_replace("appName",$data['taskInfo']['app_name'],$content);
		@file_put_contents($source_path.'/'.$data['taskid'].'.plist',$content);
	    
		//tar 文件 ipa文件
		$cmd = '/bin/bash '.$sourceDir.'/NewMvFiles.sh';
		$params = array(
						 'des'=>$source_path,
		                 'taskid'=>$data['taskid'],
						 'src'=>$IOSPACKAGE.'/packages/'.$data['taskid'],
						 'app_path'=>$des_path,
						 'plist'=>$plist,
						 'ipaUrl'=>Conf::$fileServer.'IOS/'.date("Ym").'/'.$data['taskid'].'.ipa',
						 'logoUrl'=>$data['taskInfo']['icon_image'],
						 'appName'=>$data['taskInfo']['app_name']
				 );
		$log = ROOT_PATH . '/log/script.log';
		$result = Utils::shellExec($cmd,$params,$log);
		
		$result = true;//hack to do
		//$result = FileWrapper::renameDir($source_path,$des_path);
		Timer::setEnd(__FUNCTION__);
		Timer::TRACE(__FUNCTION__, __FUNCTION__ . ' finished [result: %s ].', $result);
		return $result;
	}
	
# 1、app_taskid ：MAC电脑的密码，导入证书到钥匙链的时候用到
# 2、app_name ：应用的名称，打包ipa的时候命名所用
# 3、app_bundleid ：应用的bundle id，必须跟描述文件 证书保持一致
# 4、app_version ：应用的版本号，上架appStore时候版本
# 5、app_logopath ：应用图标
# 6、app_launchImgPath ：应用启动图
# 7、app_navigationBarColor ：应用的导航背景颜色
# 8、app_baseurl ：baseurl
# 9、app_urlpath : basepath
# 10、app_urlpath : basepath
# 11、app_channel : 渠道包名
# 12、app_appkey : appkey
# 13、app_sharekey_sina : app_sharekey_sina
# 14、app_shareSecret_sina : app_shareSecret_sina
# 15、app_shareRedirecturi_sina : app_shareRedirecturi_sina
# 16、app_shareKey_wechat : app_shareKey_wechat
# 17、app_shareSecret_wechat : app_shareSecret_wechat
# 18、app_sharekey_qq : app_sharekey_qq
# 19、app_shareSecret_qq : app_shareSecret_qq
# 20、app_jpush_appkey : jpush的appkey
# 21、app_jpush_appsecret : jpush的secret
	public function getNewParams($data){
		$newParams = array();
		$newParams['ouput_dir'] = $data['taskid'];//$1
		$newParams['app_name'] = $data['taskInfo']['app_name'];//$2
		$newParams['bundleid'] = empty($data['bundleid']) ? $data['taskInfo']['package_name'] : $data['bundleid'];//$3
		$newParams['appversion'] = $data['taskInfo']['version_name'];//$4
		$newParams['icon_image'] = $data['icon_image'];//$5
		$newParams['startup_image'] = $data['startup_image'];//$6
		$newParams['bbs_name'] = isset($data['taskInfo']['bbs_name'])?$data['taskInfo']['bbs_name']:$data['taskInfo']['app_name'];//$7
		$newParams['nav_color'] = Utils::hex2rgb($data['taskInfo']['nav_color']);//$8
		$urlInfo = Utils::parseUrl($data['api_url']);
		$newParams['baseurl'] = $urlInfo['baseurl'];//$9
		$newParams['urlpath'] = isset($urlInfo['path'])?$urlInfo['path']:'';//$10
		$newParams['channel'] = $data['taskInfo']['channel_name'];//$11
		$newParams['app_key'] = $data['app_key'];//$12
		$newParams['sharekey_sina'] = $data['share_plat'][2]['app_id'];//$13
		$newParams['shareSecret_sina'] = $data['share_plat'][2]['sec_key'];//$14
		$newParams['shareRedirecturi_sina'] = $data['share_plat'][2]['redirect_url_sina'];//$15
		$newParams['shareKey_wechat'] = $data['share_plat'][0]['app_id'];//$16
		$newParams['shareSecret_wechat'] = $data['share_plat'][0]['sec_key'];//$17
		$newParams['sharekey_qq'] = $data['share_plat'][1]['app_id'];//$18
		$newParams['shareSecret_qq'] = $data['share_plat'][1]['sec_key'];//$19
		$newParams['jpush_appkey'] = isset($data['taskInfo']['jpush_app_key']) && !empty($data['taskInfo']['jpush_app_key']) ? $data['taskInfo']['jpush_app_key'] : "b7bf5ec527d73136c7a0944c";//$20
		$newParams['jpush_appsecret'] = isset($data['taskInfo']['jpush_master_secret']) && !empty($data['taskInfo']['jpush_master_secret']) ? $data['taskInfo']['jpush_master_secret'] :"2fec24cf15dc9125d145879e";//$21
		$newParams['env'] = isset(Conf::$env)?Conf::$env:0;//todo
		return $newParams;
	}
	
}
?>
