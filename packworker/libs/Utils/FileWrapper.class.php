<?php 
class FileWrapper
{
	public static  function downloadFile($file_url, &$save_to,$check=true)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 0); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 600); 
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,3);
		curl_setopt($ch,CURLOPT_URL,$file_url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       # curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)");		
        curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36");		
		$file_content = curl_exec($ch);
		curl_close($ch);
		$result = false;
		if(!empty($file_content)){
			$downloaded_file = fopen($save_to, 'w');
			fwrite($downloaded_file, $file_content);
			fclose($downloaded_file);
			if(file_exists($save_to)){
				$result = true;
			}
			VseLog::trace(__FUNCTION__ . " succ [ file_url:$file_url,save_to: $save_to,res:$result ].");
		}else{
			VseLog::trace(__FUNCTION__ . " failed,get empty file [ file_url:$file_url,save_to: $save_to,res:$result ].");
		}
		return $result;
	}
	
	public static function tryDownloadFile($file_url,&$save_to){
		$query = parse_url($file_url,PHP_URL_QUERY);
		if(!empty($query)){
			parse_str($query);
			if(isset($url)){
				return self::downloadFile($url,$save_to);
			}
		}
		return self::downloadFile($file_url,$save_to);
	}
	/**
	 * 
	 * 尽量创建一个目录
	 * @param string $path 要创建的目录全路径
	 * @throws Exception
	 */
	public static function makeDir($path)
    {
        if(file_exists($path) && is_file($path))
        {
            throw new Exception('file ' . __FUNCTION__ . " failed, path exists but is a regular file [ path: $path ].");
        }
        if(!file_exists($path))
        {
            $oldMask = umask(0000);
            if(false === mkdir($path, 0775, true))
            {
                throw new Exception('file ' . __FUNCTION__ . " failed, cannot make dir [ path: $path ].");
            }
            umask($oldMask);
            VseLog::trace(__FUNCTION__ . " succ [ path: $path ].");
        }
    }
    
    /**
     * 
     * 根据任务信息，生成下载存储路径
     * @param string $source_url 源地址
     * @param boolean $isTemp 是否是临时目录
     */
    public static function getDownloadDir($source_url, $isTemp = false)
    {
    	is_array($source_url) && $source_url = json_encode($source_url);
    	if(true === $isTemp)
    	{
    		return Conf::$outputPath . '/' . md5($source_url) . '.tmp/' ;
    	}
    	else
    	{
    		return Conf::$outputPath . '/' . md5($source_url);
    	}
    }
    
    /**
     * 
     * 尽量递归地删除一个目录
     * @param string $dir 要删除的目录全路径
     */
	public static function removeDirectory($dir)
    {
    	$handle = null;
        if($handle = opendir("$dir"))
        {
            while(false !== ($item = readdir($handle)))
            {
                if($item !== "." && $item !== "..")
                {
                    if(is_dir("$dir/$item"))
                    {
                        self::removeDirectory("$dir/$item");
                    }
                    else
                    {
                        unlink("$dir/$item");
                        VseLog::debug("unlink a regular file [ $dir/$item ].");
                    }
                }
            }
            closedir($handle);
            rmdir($dir);
        }
        VseLog::debug(__FUNCTION__ . " succ [ dir: $dir ].");
    }
    
    /**
     * 
     * 尽量删除一个文件
     * @param string $path 要删除的文件全路径
     * @throws Exception
     */
 	public static function rmFile($path)
    {
        if(is_dir($path))
        {
            throw new Exception('file ' . __FUNCTION__ . " failed, path exists but is not a regular file [ path: $path ].");
        }
        if(file_exists($path))
        {
            if(false === unlink($path))
            {
                throw new Exception('file ' . __FUNCTION__ . " failed, unlink file failed [ path: $path ].");
            }
        }
        VseLog::trace(__FUNCTION__ . " succ [ path: $path ].");
    }
    
    /**
     * 
     * 根据任务信息，构造BT种子文件路径
     * @param array $task 任务信息
     * @param boolean $isTemp 是否是临时目录
     */
    public static function getBtSeedFilePath($task, $isTemp = false)
    {
    	return self::getDownloadDir($task['source_url'], $isTemp) . '/' . self::btSeed;
    }
    
    /**
     * 
     * 根据任务信息，构造下载数据存放路径
     * @param array $task 任务信息
     * @param boolean $isTemp 是否是临时目录
     */
    public static function getDownloadDataDir($task, $isTemp = false)
    {
    	return self::getDownloadDir($task['source_url'], $isTemp) . '/data';
    }
    
    /**
     * 
     * 尽量创建下载目录，如果是BT任务，还要下载BT任务的种子文件，为后续下载打下目录基础
     * @param array $task 任务信息
     * @param boolean $isTemp 是否是临时目录
     * @throws Exception
     */
	public static function makeTmpDownLoadDir($task, $isTemp = false)
	{
		$topDir = self::getDownloadDir($task['source_url'], $isTemp);
		self::makeDir($topDir . '/data');  //下载文件存放的最终地点
		$path = $topDir . '/' . self::taskInfo;  //任务信息
		if(file_exists($path) && !is_file($path))
        {
            throw new Exception('file ' . __FUNCTION__ . " failed, path exists but is not a regular file [ path: $path ].");
        }
        $f = fopen($path, 'w');
        if(false === $f)
        {
        	throw new Exception('file ' . __FUNCTION__ . " failed, open file failed [ path: $path ].");
        }
        fwrite($f, json_encode($task));
        fclose($f);
	}
	
	/**
	 * 
	 * 尽量移动一个目录
	 * @param string $srcPath 源地址
	 * @param string $destPath 目标地址
	 * @param boolean $overwrite 是否覆盖目标地址
	 * @throws Exception
	 */
	public static function renameDir($srcPath, $destPath, $overwrite = false)
    {
        if(!is_dir($srcPath))
        {
            throw new Exception('file ' . __FUNCTION__ . " failed, source directory does not exists [ srcPath: $srcPath ].");
        }
        if(is_file($destPath))
        {
            throw new Exception('file ' . __FUNCTION__ . " failed, dest path is not a directory [ destPath: $destPath ].");
        }
        if(is_dir($destPath) && !$overwrite)
        {
            throw new Exception('file ' . __FUNCTION__ . " failed, dest path already exists [ destPath: $destPath ].");
        }
        $destPath = trim($destPath);
        $destPath = rtrim($destPath, '/');
        self::makeDir(dirname($destPath));
        is_dir($destPath) && self::removeDirectory($destPath);
        if(false === rename($srcPath, $destPath))
        {
            throw new Exception('file ' . __FUNCTION__ . " failed, rename failed [ srcPath: $srcPath, destPath: $destPath ].");
        }
        VseLog::trace(__FUNCTION__ . " succ [ srcPath: $srcPath, destPath: $destPath, overwrite: $overwrite ].");
    }
	
	//从母包拷贝文件
	public static function copyPackageCodes($src, $des) {
		if(file_exists($des)) {
			VseLog::trace(__FUNCTION__ . 'Destination file directory created success, start copy file');
			
			$cmd = 'cp -r '. $src . ' ' . $des;
			$status = Utils::shellExec($cmd);
			
			if(!$status) {
				VseLog::trace(__FUNCTION__ . 'Destination file directory created success, copy file failed');
				return false;
			}
		} else {
			VseLog::TRACE(__FUNCTION__ . 'Destination file directory created failed');
			return false;
		}
		
		return true;
	}
};
?>
