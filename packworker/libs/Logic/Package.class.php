<?php

class Package {

	//从母包拷贝文件
	public function copyPackageCodes($src, $des, $downloadDir) {
		VseLog::TRACE('Destination file directory start to create');
		if(file_exists($des)) {
			$cmd = 'rm -rf '. $des;
			$status = Utils::shellExec($cmd);
			
			if(!$status) {
				VseLog::TRACE('Delete old file directory failed');
				return false;
			}
		}
		
		$ret = mkdir($des, 0777, true);
		
		if(!$ret) {
			VseLog::TRACE('Destination file directory create failed');
			return false;
		}
		
		if(file_exists($des)) {
			VseLog::TRACE('Destination file directory created success, start copy file');
			$cmd = 'cp -r '. $src . ' ' . $des;
			$status = Utils::shellExec($cmd);
			
			if(!$status) {
				VseLog::TRACE('Destination file directory created success, copy file failed');
				return false;
			}
			
			//创建图像目录
			VseLog::TRACE('Picture download directory start to create');
			$ret = mkdir($des . $downloadDir, 0777, true);
			
			if(!$ret) {
				VseLog::TRACE('Picture download directory create failed');
				return false;
			}
			
			if(!file_exists($des . '/Clan/download_image/')) {
				VseLog::TRACE('Picture download directory create failed');
				return false;
			}
		} else {
			VseLog::TRACE('Destination file directory created failed');
			return false;
		}
		
		return true;
	}

	//替换变量，图片裁剪
	public function beforePackage($data, $des, $downloadDir) {
		VseLog::TRACE('Start to download picture');
		
		$download_dir = $des . $downloadDir;
		
		//下载图片
		if(!$this->downloadPics($data['pic'], $download_dir)) {
			VseLog::TRACE('Download picture failed');
			return false;
		};
		
		VseLog::TRACE('Download pictures success');
		
		$android_data_array = array();
		$android_data_array[1] = $data['id'];//文件夹名称(例如：7225904945609654847123)
		$android_data_array[2] = $data['appname'];//应用名（例如： Big App4）
		$android_data_array[3] = $des . $downloadDir.'icon.png';//图标路径（例如： ../packages/15672023740755516808/Clan/download_image/icon.png）
		$android_data_array[4] = $des . $downloadDir.'recom.png';//启动图路径（例如： ../packages/15672023740755516808/download_image/recom.png）
		$android_data_array[5] = $data['bbsname'];//论坛名称（例如：我的论坛）
		//$android_data_array[6] = $data['color'];//配色，格式是“50,50,50”（例如： 50,50,50）
		//$android_data_array[7] = $data['baseurl'];//接口域名（例如：http://192.168.180.23:8080 ）
		//$android_data_array[8] = $data['path'];//接口path（例如： discuz/api/mobile/iyz_index.php）
		$android_data_array[9] = $data['channel'];//渠道包名称（例如： test channel2）
		$android_data_array[10] = $data['app_key'];//签名证书（例如： 07de6f113e7de2be9033dcffe59eecd6）
		$android_data_array[11] = $data['color_rgb'];//配色，格式是“#FF0000”（例如： #FF0000）
		$android_data_array[12] = $data['package_name'];//包名（例如：com.youzu.clan）
		$android_data_array[13] = $data['api_url'];//接口的完整的地址（例如：http://192.168.180.23:8080/discuz/api/mobile/iyz_index.php）
		$android_data_array[14] = Utils::transferPicUrl($data['pic']['icon']);//图标路径 网络地址（例如：http://192.168.180.23:8080/discuz/images/logo.png）
		//$android_data_array[15] = $this->transferPicUrl($data['pic']['recom']);//启动图路径网络地址（例如：http://192.168.180.23:8080/discuz/images/launch.png）
		$android_data_array[16] = $data['version_name'];//版本号名称
		$android_data_array[17] = $data['key_alias'];//别名
		$android_data_array[18] = $data['store_password'];//
		$android_data_array[19] = $data['key_password'];//
		$android_data_array[20] = $data['key_store_content'];//
		$android_data_array[21] = base64_decode($data['key_store_content']);//keystore content
		$android_data_array[22] = $data['share_plat'][0]['flag'];//微信
		$android_data_array[23] = $data['share_plat'][0]['app_id'];//微信
		$android_data_array[24] = $data['share_plat'][0]['sec_key'];//微信
		$android_data_array[25] = $data['share_plat'][1]['flag'];//QQ
		$android_data_array[26] = $data['share_plat'][1]['app_id'];//QQ
		$android_data_array[27] = $data['share_plat'][1]['sec_key'];//QQ
		$android_data_array[28] = $data['share_plat'][2]['flag'];//新浪微博
		$android_data_array[29] = $data['share_plat'][2]['app_id'];//新浪微博
		$android_data_array[30] = $data['share_plat'][2]['sec_key'];//新浪微博
		$android_data_array[31] = $data['share_plat'][2]['redirect_url_sina'];//新浪微博
		$android_data_array[32] = $data['build_type']; //build type
		$android_data_array[33] = intval((time() - strtotime('2015-06-01'))/3600) + 4000; // version type
		$android_data_array[34] = $data['jpush_app_key']; // jpush app key
		$android_data_array[35] = "<category android:name=\"" . $data['package_name'] . "\" />"; // jpush category
                $android_data_array[36] = "android:name=\"" . $data['package_name'] . ".permission.JPUSH_MESSAGE\" "; // jpush pemission

		//remove & in the pic url, & is illegal in android xml files
		$android_data_array[14] = str_replace("&", "\&amp;", $android_data_array[14]);
		
		foreach(Client::$androidFileReplace as $key => $value) {
			
			if($value['type'] === '1') {
				//File文件中对应字符串替换
				$targetFile = $des . '/Clan/' . $value['file'];
				$search = str_replace(',', '\,', $value['search']);
                                $replace = str_replace(',', '\,', $android_data_array[intval($value['replace'])]);
				$search = str_replace("'", "\'", $value['search']);
                                $replace = str_replace("'", "\'", $android_data_array[intval($value['replace'])]);
				

				$cmd = "sed -i ". "'s," . $search . "," . $replace . ",g' " . $targetFile;
				
				$status = Utils::shellExec($cmd);
				
				if(!$status) {
					VseLog::TRACE('String replace failed. ' . $cmd);
					return false;
				}
			} else if($value['type'] === '2') {
				//图片替换
				$sizeInfo = explode('_', $value['size']);
				$ret = Utils::resizeImage($android_data_array[intval($value['copy'])], $des . '/Clan/' . $value['file'], $sizeInfo[0], $sizeInfo[1]);
				
				if(!$ret) {
					VseLog::TRACE('Picture replace and processing failed.');
					return false;
				}
				
			} else if($value['type'] === '3') {
				//Keystore文件替换
				$tag_file = $des . "/Clan/" . $value['file'];
				$ret = file_put_contents($tag_file, $android_data_array[intval($value['replace'])]);
			
				if(!$ret) {
					VseLog::TRACE('Keystore file replace failed. ');
					return false;
				}
			}
		}
		
		$ret = Package::updatePackageName($android_data_array[12], $des);
		
		if(!$ret) {
			VseLog::TRACE('Update weixin java file failed. ');
			return false;
		} 
		
		return true;
	}
	
	//根据包名修改安卓工程文件
	//packageName
	//$des $this->packageDir . '/' . $this->dataArray['id']
	public function updatePackageName($packageName, $des) {
		$desSrc = $des . "/Clan/Clan/src/";
		
		//$packageName = "a.b.c";
		$oldPackageName = "com.youzu.clan";
		$oldPackageDir = "com/youzu/clan";
		$packageDir = str_replace(".", "/", $packageName);// a.b.c --> a/b/c
		
		$flag = file_exists($desSrc . $packageDir); 
		if(!$flag) {
			//创建对应的包名目录
			$cmd = "mkdir -p " . $desSrc . $packageDir;
			$status = Utils::shellExec($cmd);
					
			if(!$status) {
				VseLog::TRACE('Create package directory for weixin activity failed. ' . $cmd);
				return false;
			}
		}
		//id/Clan/Clan/src/a/b/c

		$targetweixinJavaFile = $desSrc . $packageDir . "/wxapi/WXEntryActivity.java";
		$flag = file_exists($targetweixinJavaFile);
 		
		if(!$flag) {		
			//拷贝java文件
			$weixinJavaDir = $desSrc . $oldPackageDir . "/wxapi/";
			$weixinJavaFile = $desSrc . $oldPackageDir . "/wxapi/WXEntryActivity.java";
			
			$flag = file_exists($weixinJavaFile);
			
			if($flag) {
				$cmd = "cp -r " . $weixinJavaDir . " " . $desSrc . $packageDir; 
				$status = Utils::shellExec($cmd);
					
				if(!$status) {
					VseLog::TRACE('Copy weixin java failed. ' . $cmd);
					return false;
				}
			} else {
				VseLog::TRACE('Old wexin java failed. ' . $cmd);
				return false;
			}
		}
		
		//修改java文件的包名
		
		$cmd = "sed -i ". "'s/com.youzu.clan/" . $packageName . "/g' " . $targetweixinJavaFile;
		$status = Utils::shellExec($cmd);
					
		if(!$status) {
			VseLog::TRACE('Update weixin java package name failed. ' . $cmd);
			return false;
		}
		
		//修改androidmanifest文件
		$targetAndroidManifestFile = $des . "/Clan/Clan/AndroidManifest.xml";
		
		$cmd = "sed -i ". "'s/cn.sharesdk.demo/" . $packageName . "/g' " . $targetAndroidManifestFile;
		$status = Utils::shellExec($cmd);
					
		if(!$status) {
			VseLog::TRACE('Update manifest filefailed. ' . $cmd);
			return false;
		}
		
		return true;
	}

	public function packageApp($androidAppHomeDir) {
		//调用gradle打包脚本
		$currentDir = getcwd();
		
		chdir($androidAppHomeDir);
		
		//$cmd1 = 'cd ' . $androidAppHomeDir;
		$cmd2 = 'gradle clean';
		$cmd3 = 'gradle build';
		
		//$status1 = Utils::shellExec($cmd1, null, null, $currentDir);
		
		$status2 = Utils::shellExec($cmd2, null, null, $currentDir);
		chdir($androidAppHomeDir);
                 
		$status3 = Utils::shellExec($cmd3, null, null, $currentDir);
	
		
		if(!$status2 || !$status3) {
			VseLog::TRACE('Run gradle script error, packaging failed');
			return false;
		}
		
		return true;	
	}
	
	public function downloadPics($pic_url, $local_dir) {
		
		if(!is_array($pic_url)) {
			VseLog::TRACE('Download pictures failed');
			return false;
		}
		
		foreach($pic_url as $key => $value) {
                        $to = realpath($local_dir).'/' . $key . '.png';
                        $ret = FileWrapper::tryDownloadFile($value, $to);
			//$ret = file_put_contents(realpath($local_dir).'/' . $key . '.png', $content);
			
			if(!$ret) {
				VseLog::TRACE('Pictures replace failed');
				return false;
			} 
		}
		
		return true;
	}
	
	public function copyFiles($src, $tmp, $des, $name) {
		
		$currentDir = getcwd();
			
		if(file_exists($tmp)) {
			$cmd = 'rm -rf '. $tmp;
			$status = Utils::shellExec($cmd);
			
			if(!$status) {
				VseLog::TRACE('Delete old tmp direcotry failed before moving apk, tar.gz files');
				return false;
			}
		}

		$ret = mkdir($tmp, 0777, true);
		
		if(!$ret) {
			VseLog::TRACE('Tmp direcotry create failed before moving apk, tar.gz files');
			return false;
		}
		
		######################################################################
		//if tar.gz file existed,remove it first
		if(file_exists($src.'/'.$name.".tar.gz")) {
			$cmd = 'rm -rf '. $src.'/'.$name.".tar.gz";
			$status = Utils::shellExec($cmd);
			
			if(!$status) {
				VseLog::TRACE('Delete old tar.gz files failed, retry it');
				return false;
			}
		}
		
		chdir($src);
		$cmd = "tar -zcvf ".$name.".tar.gz " . "./";
		$status = Utils::shellExec($cmd, null, null, $currentDir);
		
		//if(!$status) {
		//	VseLog::TRACE('Gzip the source codes failed before moving apk, tar.gz files');
		//	return false;
		//}

	        if(file_exists($src . "/Clan/Clan/build/outputs/apk/Clan-clan-release.apk")) {		
			VseLog::TRACE('APK file built success');
			
			$cmd1 =  "cp -a ". $src . "/Clan/Clan/build/outputs/apk/Clan-clan-release.apk " . $tmp . "/" . $name . ".apk";
			$status1 = Utils::shellExec($cmd1);
			$cmd2 = "cp -r ". $src . "/" . $name.".tar.gz " . $tmp;
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
		
		return true;
	}
	
}

?>
