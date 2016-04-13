<?php 
class FileWrapper
{
	public static  function downloadFile($file_url, $save_to)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 0); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 300); 
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,3);
		curl_setopt($ch,CURLOPT_URL,$file_url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
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
            @rmdir($dir);
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
};
?>
