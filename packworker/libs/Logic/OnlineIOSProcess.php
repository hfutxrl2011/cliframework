<?php
class  OnlineIOSProcess implements BaseProcess {
	public $srcDir=null;
	public function downloadIMG(&$data){
		Timer::setStart(__FUNCTION__);
		$result = true;
		if(!isset($data['os'])){
			$data['os'] = 2;
		}
		if(!isset($data['app'])){
			$data['app'] = 'DZ';
		}
		//建立目录
		$os = Conf::$osMap[$data['os']];
		$this->srcDir = ROOT_PATH.'/'.Conf::$packScript[$data['app'].'_'.$os]['packagedir'].'/'.$data['taskid'].'_online/';
		if(!file_exists($this->srcDir)){
			$result = FileWrapper::makeDir($this->srcDir);
		}
		//下载文件
		$ipa_url = $data['IOS_taskqueue']['src_ipa'];
		$ipa_url = str_replace("https://","http://",$ipa_url);
		$ipa_path = $this->srcDir.$data['taskid'].'.ipa';
		$result = FileWrapper::downloadFile($ipa_url,$ipa_path,false);
		$data['src_ipa_path'] = $ipa_path;
		
		$conf_url = $data['IOS_taskqueue']['conf_path'];
		$conf_path = $this->srcDir.$data['taskid'].'.mobileprovision';
		$result = FileWrapper::downloadFile($conf_url,$conf_path,false);
		$data['conf_path'] = $conf_path;
		
		$cert_url = $data['IOS_taskqueue']['cert_path'];
		$cert_path = $this->srcDir.$data['taskid'].'.p12';
		$result = FileWrapper::downloadFile($cert_url,$cert_path,false);
		$data['cert_path'] = $cert_path;
		
		Timer::setEnd(__FUNCTION__);
		Timer::TRACE(__FUNCTION__, __FUNCTION__ . ' finished [result: %s ].', $result);
		return $result;
	}
	public function execShell(&$data){
		Timer::setStart(__FUNCTION__);
		$result = true;
		if('WP'==$data['app']){
			$sourceDir = Conf::$packScript['WP_IOS']['onlinescriptdir'];
			$params = $this->getParams($data);
			$cmd = 'cd '.$sourceDir.' && chmod 777 appstore_pack_online.sh && ./appstore_pack_online.sh';
			$result = Utils::shellExec($cmd,$params);
		}else{
			$sourceDir = ROOT_PATH.'/'.Conf::$packScript['DZ_IOS']['sourcedir'];
			$params = $this->getParams($data);
			$cmd = 'cd '.$sourceDir.' && chmod 777 resign_ipa.sh && ./resign_ipa.sh';
			$result = Utils::shellExec($cmd,$params);
		}
		Timer::setEnd(__FUNCTION__);
		Timer::TRACE(__FUNCTION__, __FUNCTION__ . ' finished [result: %s ].', $result);
		return $result;
	}
	
	
# ./resign_ipa.sh "001" "小香网" "com.mooxiang.jingyou" "2.0" "47" "/Users/Jessise/Desktop/bigapp02_cert.p12" "123456" "/Users/Jessise/Desktop/个人证书重签名/dis_xiaoxiangwang.mobileprovision" "/Users/Jessise/Desktop/tuisong.ipa"
#***********************************************************************************
# 1、mac_password ：MAC电脑的密码，导入证书到钥匙链的时候用到
# 2、app_name ：应用的名称，打包ipa的时候命名所用
# 3、app_bundleid ：应用的bundle id，必须跟描述文件 证书保持一致
# 4、app_version ：应用的版本号，上架appStore时候版本
# 5、app_build_version ：应用的build版本号，每个version对应的build号都是依次累加的 不可重复，否则上传store失败
# 6、cert_path ：发布证书p12文件的路径，一定注意是.p12后缀
# 7、cert_secretkey ：发布证书p12文件导出时候的秘钥，这里做导入p12文件到loginkeychain的时候用到
# 8、cert_provision_path ：描述文件路径，校验证书和bundleid的一个文件，打包必须参数
# 9、project_path : 母包路径
#***********************************************************************************
    public function getParams($data){
		$params = array();
		$params['mac_password'] = '11111111';
		//$params['app_name'] = isset($data['taskInfo']['app_name'])?$data['taskInfo']['app_name']:'2';
		$params['app_name'] = isset($data['taskid'])?$data['taskid'].'_online':'2';
		$params['app_bundleid'] = isset($data['IOS_taskqueue']['bundleid'])?$data['IOS_taskqueue']['bundleid']:'3';
		$params['app_version'] = isset($data['IOS_taskqueue']['version'])?$data['IOS_taskqueue']['version']:'4';
		$params['app_build_version'] = isset($data['IOS_taskqueue']['buildid'])?$data['IOS_taskqueue']['buildid']:'5';
		$params['cert_path'] = isset($data['cert_path'])?$data['cert_path']:'6';
		$params['cert_secretkey'] = isset($data['IOS_taskqueue']['cert_key'])?$data['IOS_taskqueue']['cert_key']:'7';
		$params['cert_provision_path'] = isset($data['conf_path'])?$data['conf_path']:'8';
		$params['project_path'] = isset($data['src_ipa_path'])?$data['src_ipa_path']:'9';
		return $params;
	}

	public function doProcess(&$data,$engine=null){
	
		
		$result = $this->downloadIMG($data);
		$engine->updateStatus(3,$result);
		
		$result = $this->execShell($data);
		$engine->updateStatus(4,$result);
		
		$result = $this->moveFiles($data);
		$engine->updateStatus(5,$result);
		
	}
	
	public function moveFiles(&$data){
		Timer::setStart(__FUNCTION__);
		$result = true;
		
		//文件移动
		$os = Conf::$osMap[$data['os']];
		$des_path = ROOT_PATH.'/'.Conf::$packScript[$data['app'].'_'.$os]['appdir'];
		
		//plist文件
		$sourceDir = ROOT_PATH.'/'.Conf::$packScript[$data['app'].'_'.$os]['sourcedir'];
		$plist = $sourceDir.'/clan.plist';
		
		$content = @file_get_contents($plist);
		$data['taskInfo']['icon_image'] = str_replace("&",'&amp;',$data['taskInfo']['icon_image']);
		$content = str_replace("logoUrl",$data['taskInfo']['icon_image'],$content);
		$content = str_replace("ipaUrl",Conf::$fileServer4Os.'IOS/'.date("Ym").'/'.$data['taskid'].'_online.ipa',$content);
		$content = str_replace("appName",$data['taskInfo']['app_name'],$content);
		$content = str_replace("com.youzu.test",$data['IOS_taskqueue']['bundleid'],$content);
		$content = str_replace("com.youzu.bigwords.sit",$data['IOS_taskqueue']['bundleid'],$content);
		
		@file_put_contents($this->srcDir.$data['taskid'].'_online.plist',$content);
	    
		if('WP' == $data['app']){
			$IOSDir = Conf::$packScript['WP_IOS']['onlinescriptdir'].'packages/';
		}else{
			$IOSDir = ROOT_PATH.'/'.Conf::$packScript[$data['app'].'_'.$os]['sourcedir'].'/packages/';
		}
		
		//tar 文件 ipa文件
		$cmd = '/bin/bash '.$sourceDir.'/onlineMvFiles.sh';
		$params = array(
						 'src'=>rtrim($this->srcDir,'/'),
						 'app_path'=>$des_path,
						 'taskid'=>$data['taskid'],
						 'IOSDir'=>$IOSDir,
				 );
		$log = ROOT_PATH . '/log/online_script.log';
		$result = Utils::shellExec($cmd,$params,$log);
        $result = true; //hack
		Timer::setEnd(__FUNCTION__);
		Timer::TRACE(__FUNCTION__, __FUNCTION__ . ' finished [result: %s ].', $result);
		return $result;
	}
}
?>
