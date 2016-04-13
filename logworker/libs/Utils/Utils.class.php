<?php
class Utils {
	
	public static function cutLog($file,$time){
		$cutFile = $file.date("mdH",time()-$time);
		if(!file_exists($cutFile)){
			if( false === rename($file,$cutFile) ){
				VseLog::TRACE(__FUNCTION__ .' rename failed,file:'.$file);
			}
			VseLog::TRACE(__FUNCTION__ .' rename succ,file:'.$file);
		}else{
			VseLog::TRACE(__FUNCTION__ .' file already exist:'.$file);
		}
	}
	
	public static function backupLog($file,$time){
		$basePath = dirname($file);
		$yes = strtotime(date("Y-m-d"))-3600*24;
		if(!file_exists($basePath.'/bak')){
			mkdir($basePath.'/bak');
		}
		if(!file_exists($basePath.'/bak/'.date("Ymd"))){
			mkdir($basePath.'/bak/'.date("Ymd"));
		}
		if(!file_exists($basePath.'/bak/'.date("Ymd",$yes))){
			mkdir($basePath.'/bak/'.date("Ymd",$yes));
		}
		foreach (glob($file.'*') as $filename) {
			$mtime = filemtime($filename);
			$path_parts = pathinfo($filename);
			$desFile = $basePath.'/bak/'.date("Ymd",$yes).'/'.$path_parts['basename'];
			$tomorrow = strtotime(date("Y-m-d"))+3600*24;
			if($tomorrow-$mtime > $time && !file_exists($desFile)){
				if( false === rename($filename,$desFile) ){
					VseLog::TRACE(__FUNCTION__ .' rename failed,file:'.$filename);
				}
				VseLog::TRACE(__FUNCTION__ .' rename succ,file:'.$filename.',des file:'.$desFile);
			}else{
				VseLog::TRACE(__FUNCTION__ .' do not need remove,file:'.$filename);
			}
		}
	}
	
	public static function removeLog($file,$time){
		$basePath = dirname($file);
		if(!file_exists($basePath.'/bak')){
			mkdir($basePath.'/bak');
		}
		foreach (glob($basePath.'/bak/*') as $filename) {
			$mtime = filemtime($filename);
			if(time()-$mtime > $time && file_exists($filename)){
				if( false === FileWrapper::removeDirectory($filename) ){
					VseLog::TRACE(__FUNCTION__ .' removeDirectory failed,file:'.$filename);
				}
				VseLog::TRACE(__FUNCTION__ .' removeDirectory succ,file:'.$filename.',filemtime:'.$mtime);
			}else{
				VseLog::TRACE(__FUNCTION__ .' do not need removeDirectory,file:'.$filename);
			}
		}
	}
	
	public static function removeDir($file,$time){
		foreach (glob($file) as $filename) {
			$mtime = filemtime($filename);
			if(time()-$mtime > $time && file_exists($filename)){
				if( false === FileWrapper::removeDirectory($filename) ){
					VseLog::TRACE(__FUNCTION__ .' removeDirectory failed,file:'.$filename);
				}
				VseLog::TRACE(__FUNCTION__ .' removeDirectory succ,file:'.$filename.',filemtime:'.$mtime);
			}else{
				VseLog::TRACE(__FUNCTION__ .' do not need removeDirectory,file:'.$filename);
			}
		}
	}
	
	public static function removeFile($file,$time){
		foreach (glob($file) as $filename) {
			$mtime = filemtime($filename);
			if(time()-$mtime > $time && file_exists($filename)){
				if( false === FileWrapper::rmFile($filename) ){
					VseLog::TRACE(__FUNCTION__ .' rmFile failed,file:'.$filename);
				}
				VseLog::TRACE(__FUNCTION__ .' rmFile succ,file:'.$filename.',filemtime:'.$mtime);
			}else{
				VseLog::TRACE(__FUNCTION__ .' do not need rmFile,file:'.$filename);
			}
		}
	}
	
    //android 用
	public static function shellExec($cmd,$params=null,$outputLog=null, $workdir=null) {
		$cmd = self::cmdLine($cmd,$params);
		if($outputLog != null){
			$cmd .= " >>".$outputLog;
		}else{
			$cmd .= " >>". ROOT_PATH . '/log/script.log';
		}
		VseLog::trace(__FUNCTION__ . " start [ cmd: $cmd ].");
        $output = exec($cmd,$out,$status);
		VseLog::trace(__FUNCTION__ . " end [ cmd: $cmd,status:$status,output:$output ].");
		return $status?false:true;
	}
	//IOS 用
	public static function shellExec2($cmd,$params=null,$outputLog=null, $workdir=null) {
		$cmd = self::cmdLine($cmd,$params);
		if($outputLog != null){
			$cmd .= " >>".$outputLog;
		}else{
			//$cmd .= " >>". ROOT_PATH . '/log/script.log';
		}
        
		VseLog::trace(__FUNCTION__ . " start [ cmd: $cmd ].");
        $output = exec($cmd,$out,$status);
		VseLog::trace(__FUNCTION__ . " end [ cmd: $cmd,status:$status,output:$output ].");
		return $status?false:true;
	}
	
	public static function cmdLine($cmd,$params=null){
		if(!empty($params))
		foreach($params as $k=>$v){
			$cmd .= " '$v'";
		}
		return $cmd;
	}
	
	//http://mobfile.youzu.com/show?pic=Uploads_image/2/e/a/d/eadf1eda9529216516c1af5a5dd3fa5f.png&size=1024_1024
	//http://mobfile.youzu.com/show/Uploads_image/2/e/a/d/eadf1eda9529216516c1af5a5dd3fa5f_1024_1024.png
	public static function transferPicUrl($oldUrl) {
                if(strpos($oldUrl, 'mobfile.youzu.com/show?pic=Uploads_image') === false){
			return $oldUrl;
		}
		$urlInfo = parse_url($oldUrl);
		
		$newUrl = $urlInfo['scheme'] . "://" . $urlInfo['host'];
		$queryInfo = Utils::convertUrlQuery($urlInfo['query']);

		$newurlInfo = parse_url($newUrl. $urlInfo['path'] . "/" . $queryInfo['pic']);
		$ext = explode(".", $newurlInfo['path']);

       $newUrl = $newUrl . $ext[0] . "_" . $queryInfo['size'] . "." . $ext[1];	
		return $newUrl;
	}
	
	public static function convertUrlQuery($query) {	
		
                $queryParts = explode('&', $query);

		$params = array();
		foreach ($queryParts as $param)
		{
				$item = explode('=', $param);
				$params[$item[0]] = $item[1];
		}

		return $params;
	}
	
	/**
	 * 改变图片的宽高
	 * 
	 * @param string $img_src 原图片的存放地址或url 
	 * @param string $new_img_path 新图片的存放地址 
	 * @param int $new_width 新图片的宽度 
	 * @param int $new_height 新图片的高度
	 * @return bool 成功true, 失败false
	 */
	public static function resizeImage($img_src, $new_img_path, $new_width, $new_height) {
		//var_dump($img_src);
		//var_dump($new_img_path) or die();
		$img_info = @getimagesize($img_src);
		if (!$img_info || $new_width < 1 || $new_height < 1 || empty($new_img_path)) {
			return false;
		}
		if (strpos($img_info['mime'], 'jpeg') !== false) {
			$pic_obj = imagecreatefromjpeg($img_src);
		} else if (strpos($img_info['mime'], 'gif') !== false) {
			$pic_obj = imagecreatefromgif($img_src);
		} else if (strpos($img_info['mime'], 'png') !== false) {
			$pic_obj = imagecreatefrompng($img_src);
		} else {
			return false;
		}

		$pic_width = imagesx($pic_obj);
		$pic_height = imagesy($pic_obj);

		if (function_exists("imagecopyresampled")) {
			$new_img = imagecreatetruecolor($new_width,$new_height);
			imagecopyresampled($new_img, $pic_obj, 0, 0, 0, 0, $new_width, $new_height, $pic_width, $pic_height);
		} else {
			$new_img = imagecreate($new_width, $new_height);
			imagecopyresized($new_img, $pic_obj, 0, 0, 0, 0, $new_width, $new_height, $pic_width, $pic_height);
		}
		if (preg_match('~.([^.]+)$~', $new_img_path, $match)) {
			$new_type = strtolower($match[1]);
			switch ($new_type) {
				case 'jpg':
					imagejpeg($new_img, $new_img_path);
					break;
				case 'gif':
					imagegif($new_img, $new_img_path);
					break;
				case 'png':
					imagepng($new_img, $new_img_path);
					break;
				default:
					imagejpeg($new_img, $new_img_path);
			}
		} else {
			imagejpeg($new_img, $new_img_path);
		}
		imagedestroy($pic_obj);
		imagedestroy($new_img);
		return true;
	}
	
	public static function getRunningSystemOSType() {
		return strtoupper(substr(PHP_OS,0,3));
	}
	
	public static function initEnv(){
		if(PHP_OS === 'Linux'){
			Conf::$packOS = 1;
		}
		if(PHP_OS === 'Darwin'){
			Conf::$packOS = 2;
		}
		Conf::$packSvr = self::getHost(Conf::$packOS);
	}

        public static function DataConverted($beforeData) {
		$afterData = array();
		
		$afterData['id'] = $beforeData['taskid'];
		$afterData['api_url'] = $beforeData['api_url'];
		$afterData['channel'] = $beforeData['taskInfo']['channel_name'];
		$afterData['package_name'] = $beforeData['taskInfo']['package_name'];
		$afterData['version_name'] = $beforeData['taskInfo']['version_name'];
		$afterData['app_key'] = $beforeData['app_key'];
		$afterData['color_rgb'] = $beforeData['taskInfo']['nav_color'];
		$afterData['appname'] = $beforeData['taskInfo']['app_name'];
		$afterData['bbsname'] = $beforeData['taskInfo']['bbs_name'];
		$afterData['type'] = $beforeData['yz_mob_taskqueue'][0]['os'] == 1 ? 'android' : 'ios';
		$afterData['pic']['icon'] = $beforeData['taskInfo']['icon_image'];
		$afterData['pic']['recom'] = $beforeData['taskInfo']['startup_image'];
		$afterData['app_id'] = $beforeData['app_id'];
		$afterData['key_alias'] = $beforeData['key_alias'];
		$afterData['store_password'] = $beforeData['store_password'];
		$afterData['key_password'] = $beforeData['key_password'];
		$afterData['key_store_content'] = $beforeData['key_store_content'];
		$afterData['share_plat'] = $beforeData['share_plat'];
		$afterData['build_type'] = $beforeData['build_type'];
		
		//		var_dump($afterData) or die();
		return $afterData;
	}
	
	public static function hex2rgb($colour) {
		if ( $colour[0] == '#' ) {
			$colour = substr( $colour, 1 );
		}
		if ( strlen( $colour ) == 6 ) {
			list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
		} elseif ( strlen( $colour ) == 3 ) {
			list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
		} else {
			return false;
		}
		$r = hexdec( $r );
		$g = hexdec( $g );
		$b = hexdec( $b );
		return $r.','.$g.','.$b;
		 //return array( 'red' => $r, 'green' => $g, 'blue' => $b );
	}
	
	public static function parseUrl($apiUrl){
		$params['api_url'] = $apiUrl;//baseurl+path
		$port = parse_url($apiUrl, PHP_URL_PORT);
		if(!empty($port)){
			$port = ':'.$port.'';
		}else{
			$port = '';
		}
		$params['baseurl'] = parse_url($apiUrl, PHP_URL_SCHEME) . '://' . parse_url($apiUrl, PHP_URL_HOST) . $port ;
		
		$query = parse_url($apiUrl, PHP_URL_QUERY);
		if(!empty($query)){
			$query = '?'.$query;
		}else{
			$query = '';
		}
		$params['path'] = parse_url($apiUrl, PHP_URL_PATH) . $query;
		$params['path'] = trim($params['path'],'/');
		return $params;
	}
	
	public static function getImgExt($file_url){
		$img = @getimagesize($file_url);
		$ext = '.png';
		if(isset($img['mime']) && $img['mime'] = 'image/jpeg'){
			$ext = '.jpg';
		}else if(isset($img['mime']) && $img['mime'] = 'image/png'){
			$ext = '.png';
		}else if(isset($img['mime']) && $img['mime'] = 'image/gif'){
			$ext = '.gif';
		}else{
			$ext = '.png';
		}
		return $ext;
	}
	
	public static function getHost($os){
		$ip = "127.0.0.1";
		if(1 == $os){
			$cmd = "ifconfig | grep 'inet addr' | grep -v '127.0.0.1' | awk -F ' ' '{print $2}'|awk -F ':' '{print $2}'";
		}
		if(2 == $os){
			$cmd = "ifconfig | grep inet | grep -v '127.0.0.1'  | grep -v 'inet6'| awk -F ' ' '{print $2}'";
		}
		if($os == 1 || $os == 2){
			$ip = exec($cmd,$out,$status);
			if($ip && !filter_var($ip, FILTER_VALIDATE_IP)){
				$ip = "127.0.0.1";
			}
		}
		return $ip;
	}
	
	public static function setCmdTimeout($cmd,$params=null,$outputLog=null, $timeout=900){
		$result = true;
		if(extension_loaded("pcntl")){
			$pid = pcntl_fork();
			if(0 === $pid){
				//子进程
				posix_setpgid(0, 0);
				$cmdLog = self::cmdLine($cmd,$params);
				$params = array_values($params);
				array_unshift($params,$cmd);
				require_once dirname(dirname(dirname(__FILE__))).'/libs/Log/Log.php';
		        VseLog::init(Conf::$vseLogInfo);
				VseLog::trace(__FUNCTION__ . " start child process >>>> [ log file:".Conf::$vseLogInfo['file']." ].");				
				VseLog::trace(__FUNCTION__ . " start child process >>>> [ cmd: $cmdLog, params:".json_encode($params)." ].");
				pcntl_exec('/bin/bash', $params);
			}
			sleep(1);
			//父进程继续执行
			while($timeout--){
				$ret = pcntl_waitpid(-$pid, $status, WNOHANG);
				VseLog::trace(__FUNCTION__ . ">>> wait returned [ ret: $ret ]");
				if(0 >= $ret ){
					if(pcntl_wifsignaled($status)){
                        VseLog::trace(__FUNCTION__ . " signal received, continue [ $status ]...");
                        sleep(1);
                        continue;
                    }
					if(pcntl_wifstopped($status)){
                        VseLog::trace(__FUNCTION__ . " process stopped, break [ $status ]...");
                        break;
                    }
					sleep(1);
					continue;
				}
				$ret = pcntl_wexitstatus($status);//status to do
				VseLog::trace(__FUNCTION__ . " pcntl_wexitstatus [ ret: $ret  ].");
				$result = $ret ? false : true;
				break;
			}
			if($timeout < 0){
				$ret = posix_kill(-$pid, SIGKILL);
				VseLog::trace(__FUNCTION__ . " $cmd timeout, posix_kill succ [ ret: $ret ,pid:$pid  ].");
				$result = false;
			}
		}else{
			VseLog::trace(__FUNCTION__ . " pcntl extension loaded failed.");
			$cmd  = '/bin/bash ' . $cmd;
			$result = self::shellExec2($cmd,$params,$outputLog);
		}
		return $result;
	}
	
}
?>
