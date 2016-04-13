#!/usr/bin/env php
<?php

ini_set('date.timezone','Asia/Shanghai');
define('ROOT_PATH' , dirname(dirname(__FILE__)));
define('LIB_PATH' , ROOT_PATH."/libs/");
define('CONF_PATH' , ROOT_PATH."/conf/");
set_include_path( LIB_PATH. PATH_SEPARATOR .get_include_path() );


$mode = 'Prod';
require_once CONF_PATH.$mode.'.Config.class.php';
require_once 'Log/Log.php';
require_once 'FrameWork.class.php';
require_once 'MainLogic.class.php';
require_once 'Utils/FileWrapper.class.php';
require_once 'Utils/Sign.class.php';
require_once 'Utils/Timer.class.php';
//require_once 'Utils/Utils.class.php';
require_once 'DataEngine/DataEngine.class.php';


class UploadWorker
{
    private $_product = 1;            //!< 1:Discuz 2:WordPress
    private $_os = 1;                 //!< 1:Android, 2:Ios
    private $_monitor_path = "";      //!< 监控的目录
    private $_work_path = "";         //!< 工作目录
    private $_db = NULL;
    private $_upload_bin = "";
    private $_file_type_map = array(
        "apk_tar" => "APK_TAR",
        "ios_tar" => "IOS_TAR",
        "apk" => "APK",
        "ios" => "IOS",
        "iosplist" => "IOSPLIST",
    );
    

    public function __construct($product, $os)
    {
        $this->init($product, $os);
        $this->_db = new DataEngine();
        if (!$this->_db) {
            VseLog::warning("new DataEngine fail");
            exit(0);
        }
    }

    public function debug() 
    {/*{{{*/
        $conf = array (
            "_product" => $this->_product,
			"_os" => $this->_os,
			"_monitor_path" => $this->_monitor_path,
			"_work_path" => $this->_work_path,
        );
        print_r($conf);
    }/*}}}*/

    public function run()
    {/*{{{*/
        while (true) 
        {
            clearstatcache();
            $dirs = glob($this->_monitor_path."/*", GLOB_ONLYDIR);
            if (count($dirs)>0) {
                shuffle($dirs);
                foreach ($dirs as $dir) {
                    $this->procTask(basename($dir));
                }
            }
            
/*
            $cmd = "rm -rf ".$this->_work_path;
			$res = system($cmd);
			VseLog::trace("$cmd [res:$res]");
*/
            exit(0);
            sleep(1);
        }
    }/*}}}*/

    ///////////////////////////////////
    private function init($product, $os)
    {/*{{{*/
        $this->_product = $product;
        $this->_os = $os;
        
        $pid = getmypid();
        $uploadConf = &Conf::$uploadConf;
        $pn = $this->_product==1 ? "DZ" : "WP";
        $on = $this->_os==1 ? "Android" : "IOS";
        $key = $pn."_".$on;
        $this->_upload_bin = $uploadConf["upload_bin"];

        // 准备监控目录和工作目录
        $this->_monitor_path = $uploadConf["monitor_path"][$key];
        $this->_work_path    = $uploadConf["work_path"][$key];
        if (!is_dir($this->_work_path)) {
            mkdir($this->_work_path, 0777, true);
        }

        // 初始化日志
		Conf::$vseLogInfo['file'] = dirname(dirname(__FILE__)) . '/log/uploader.log';
		VseLog::init(Conf::$vseLogInfo);
        $logStr = "upload worker is run [pid:$pid] [os:$os] ".
                  "[monitor_path:".$this->_monitor_path."] ".
                  "[work_path:".$this->_work_path."]";
        VseLog::trace($logStr);
    }/*}}}*/

    // 处理一个任务
    private function procTask($dirname)
    {
        //1. 准备task文件和目录
        $arr = explode("_",$dirname);
        if (count($arr)==1) {return;}  //!< 不带_online的目录不处理
        $taskid = $arr[0];
        $workinfo = $this->prepareTaskWorkPath($taskid);
        if ($workinfo === false) {
			$this->afterProcTask($taskid);
			return;
        }
        
        //2. 上传安装包
/*
        $app_path = "";
        $plist_path = "";
        if ($this->_os == 1) {
			if (!$this->uploadDir($workinfo["apk"]["upload_path"])) {
				$this->afterProcTask($taskid);
				return;
            }
            $app_path = $workinfo["apk"]["download_url"];
        } else {
			if (!$this->uploadDir($workinfo["ios"]["upload_path"]) ||
                !$this->uploadDir($workinfo["iosplist"]["upload_path"])) {
				$this->afterProcTask($taskid);
				return;
            }
            $app_path = $workinfo["ios"]["download_url"];
			$plist_path = $workinfo["iosplist"]["download_url"];
        }
        $this->updateDBAfterUploadPkg($app_path, $plist_path, $taskid);
        //echo "upload package and upadte db success\n";
*/      

        // 上传online包
        if ($this->_os == 2) {
            $des_app_path = "";
            $des_plist_path = "";
            if (!$this->uploadDir($workinfo["ios_online"]["upload_path"]) ||
                !$this->uploadDir($workinfo["iosplist_online"]["upload_path"])) {
				$this->afterProcTask($taskid);
				return;
            }
			$des_app_path = $workinfo["ios_online"]["download_url"];
			$des_plist_path = $workinfo["iosplist_online"]["download_url"];
			$this->updateDBAfterUploadPkgOnline($des_app_path, $des_plist_path, $taskid);
			VseLog::trace("upload ios package online and update db success\n");
        }

        //3. 上传源码包
        $code_path = "";
		/*
		if ($this->_os == 1) {
			if (!$this->uploadDir($workinfo["apk_tar"]["upload_path"])) {
				$this->afterProcTask($taskid);
				return;
            }
            $code_path = $workinfo["apk_tar"]["download_url"];
        } else {
			if (!$this->uploadDir($workinfo["ios_tar"]["upload_path"])) {
				$this->afterProcTask($taskid);
				return;
            }
            $code_path = $workinfo["ios_tar"]["download_url"];
        }
        $this->updateDBAfterUploadTar($code_path, $taskid);
        echo "upload tar and update db success\n";
		*/
        
		$this->debug();
        print_r($workinfo);

        /////////////////////////////////
        // 回调
        $this->callback($taskid);
        /////////////////////////////////

        //4. 收尾工作
		$this->afterProcTask($taskid);
        usleep(100000);
//exit(0);
    }

    // 准备task的工作目录并拷贝文件
    private function prepareTaskWorkPath($taskid)
    {/*{{{*/
        $root = $this->_work_path."/".$taskid."_online";
        $fmap = &$this->_file_type_map;
        $uploadConf = &Conf::$uploadConf;
        $pfix = date("Ym");
        $task_work_path = array (
            "root" => $root,
            "apk_tar" => array (
                "monitor_file" => $this->_monitor_path."/".$taskid."/".$taskid.".tar.gz",
                "local_path"   => $root."/".$fmap["apk_tar"]."/$pfix",
                "local_file"   => $root."/".$fmap["apk_tar"]."/$pfix"."/".$taskid.".tar.gz",
                "upload_path"  => $root."/".$fmap["apk_tar"],
                "download_url" => $uploadConf["download_root"]."/".$fmap["apk_tar"]."/$pfix"."/".$taskid.".tar.gz",
            ),
            "apk" => array (
                "monitor_file" => $this->_monitor_path."/".$taskid."/".$taskid.".apk",
                "local_path"   => $root."/".$fmap["apk"]."/$pfix",
                "local_file"   => $root."/".$fmap["apk"]."/$pfix"."/".$taskid.".apk",
                "upload_path"  => $root."/".$fmap["apk"],
                "download_url" => $uploadConf["download_root"]."/".$fmap["apk"]."/$pfix"."/".$taskid.".apk",
            ),
            "ios_tar" => array(
                "monitor_file" => $this->_monitor_path."/".$taskid."/".$taskid.".tar.gz",
                "local_path"   => $root."/".$fmap["ios_tar"]."/$pfix",
                "local_file"   => $root."/".$fmap["ios_tar"]."/$pfix"."/".$taskid.".tar.gz",
                "upload_path"  => $root."/".$fmap["ios_tar"],
                "download_url" => $uploadConf["download_root"]."/".$fmap["ios_tar"]."/$pfix"."/".$taskid.".tar.gz",
            ),
            "ios" => array (
                "monitor_file" => $this->_monitor_path."/".$taskid."/".$taskid.".ipa",
                "local_path"   => $root."/".$fmap["ios"]."/$pfix",
                "local_file"   => $root."/".$fmap["ios"]."/$pfix"."/".$taskid.".ipa",
                "upload_path"  => $root."/".$fmap["ios"],
                "download_url" => $uploadConf["download_root"]."/".$fmap["ios"]."/$pfix"."/".$taskid.".ipa",
            ),
            "iosplist" => array (
                "monitor_file" => $this->_monitor_path."/".$taskid."/".$taskid.".plist",
                "local_path"   => $root."/".$fmap["iosplist"]."/$pfix",
                "local_file"   => $root."/".$fmap["iosplist"]."/$pfix"."/".$taskid.".plist",
                "upload_path"  => $root."/".$fmap["iosplist"],
                "download_url" => $uploadConf["download_root"]."/".$fmap["iosplist"]."/$pfix"."/".$taskid.".plist",
            ),
            ///////////////////////////////////////
            "ios_online" => array (
                "monitor_file" => $this->_monitor_path."/".$taskid."_online/".$taskid."_online.ipa",
                "local_path"   => $root."/".$fmap["ios"]."/$pfix",
                "local_file"   => $root."/".$fmap["ios"]."/$pfix"."/".$taskid."_online.ipa",
                "upload_path"  => $root."/".$fmap["ios"],
                "download_url" => $uploadConf["download_root"]."/".$fmap["ios"]."/$pfix"."/".$taskid."_online.ipa",
            ),
            "iosplist_online" => array (
                "monitor_file" => $this->_monitor_path."/".$taskid."_online/".$taskid."_online.plist",
                "local_path"   => $root."/".$fmap["iosplist"]."/$pfix",
                "local_file"   => $root."/".$fmap["iosplist"]."/$pfix"."/".$taskid."_online.plist",
                "upload_path"  => $root."/".$fmap["iosplist"],
                "download_url" => $uploadConf["download_root"]."/".$fmap["iosplist"]."/$pfix"."/".$taskid."_online.plist",
            ),
            ///////////////////////////////////////
        );

        //$keys = array("ios_tar", "ios", "iosplist", "ios_online", "iosplist_online");
        $keys = array("ios_online", "iosplist_online");
        if ($this->_os == 1) {
            $keys = array("apk_tar", "apk");
        }
        foreach ($keys as $key) {
            $item = $task_work_path[$key];
            if (isset($item["local_path"]) && !is_dir($item["local_path"]))
                mkdir($item["local_path"], 0777, true);
            $this->movefile($item["monitor_file"], $item["local_file"]);
            if (!is_file($item["local_file"])) {
                VseLog::warning("file does not exist [".$item["local_file"]."]");
                return false;
            }
        }
        return $task_work_path;
    }/*}}}*/

    // 将文件上传到文件服务器
    private function uploadDir($sourcedir)
    {/*{{{*/
        $upload_root = Conf::$uploadConf["upload_root"];
        $cmd = $this->_upload_bin." $sourcedir $upload_root";
        @exec($cmd, $output, $res);
        VseLog::trace("$cmd [res:$res]");
        if ($res != 0) {
            $msg = implode("|",$output);
            VseLog::warning("upload file fail [$sourcedir] [res:$res] [$msg]");
            return false;
        }
        return true;
    }/*}}}*/

    // 移动文件 
    private function movefile($source, $dest) 
    {/*{{{*/
        rename($source,$dest);
        VseLog::trace("rename $source $dest");
    }/*}}}*/

    // 上传完安装包文件后更新数据库
    public function updateDBAfterUploadPkg($app_path, $plist_path, $taskid)
    {/*{{{*/
        $os = $this->_os;
        $status = 6;
        $this->_db->getConnection();
        $this->_db->startTransaction(false);
		$sql = "UPDATE dev_app_task SET app_path='$app_path', plist_path='$plist_path', status=$status ".
               "WHERE taskid='$taskid' and os=$os";
        VseLog::trace($sql);
        $this->_db->query($sql);
        $mtime = time();
        $sql = "UPDATE yz_mob_taskqueue SET status=$status, mtime=$mtime WHERE taskid='$taskid' and os=$os";
        VseLog::trace($sql);
        $this->_db->query($sql);
		$this->_db->endTransaction(true);
    }/*}}}*/

	// 上传完IOS安装包online文件后更新数据库
    public function updateDBAfterUploadPkgOnline($app_path, $plist_path, $taskid)
    {/*{{{*/
        $os = $this->_os;
        $status = 6;
        $this->_db->getConnection();
        $this->_db->startTransaction(false);
		$sql = "UPDATE dev_app_ios SET des_ipa='$app_path', plist_path='$plist_path', status=$status ".
               "WHERE taskid='$taskid'";
        VseLog::trace($sql);
        $this->_db->query($sql);
        //////////////////////////////////
		$sql = "UPDATE dev_ios_shelf set status=6, reason='打包成功' ".
               "WHERE taskid='$taskid'";
        /////////////////////////////////////////////////////////////////////
        // 短信通知
        $url = "http://bigapp.youzu.com/mc/mis/misSendMsg?taskid=$taskid";
        $this->httpRequest($url);
        //////////////////////////////////////////////////////////////////////
        VseLog::trace($sql);
        $this->_db->query($sql);
        //////////////////////////////////
		$this->_db->endTransaction(true);
    }/*}}}*/

    // 上传完源码包文件后更新数据库
    public function updateDBAfterUploadTar($code_path, $taskid)
    {/*{{{*/
		$os = $this->_os;
		$sql = "UPDATE dev_app_task SET code_path='$code_path' WHERE taskid='$taskid' and os=$os";
        VseLog::trace($sql);
        $this->_db->query($sql);
    }/*}}}*/

    // 一个任务完成后的处理逻辑
    public function afterProcTask($taskid)
    {/*{{{*/
        if (!Conf::$uploadConf["save_work_path"]) {
            $work_taskdir = $this->_work_path."/".$taskid."_online";
			$cmd = "rm -rf $work_taskdir";
			$res = system($cmd);
			VseLog::trace("$cmd [res:$res]");
        }
        $monit_task_path = $this->_monitor_path."/".$taskid;
        if (is_dir($monit_task_path)) {
			$cmd = "rm -rf $monit_task_path";
			$res = system($cmd);
			VseLog::trace("$cmd [res:$res]");
        }
        $monit_task_path.= "_online";
        if (is_dir($monit_task_path)) {
			$cmd = "rm -rf $monit_task_path";
			$res = system($cmd);
			VseLog::trace("$cmd [res:$res]");
        }
		clearstatcache();

        //////////////////////////////////////////////////////////
        // 如果数据表status!=6，表示任务上传失败，将status设为0
        $os = $this->_os;
        $status = 6;
        $this->_db->getConnection();
        $this->_db->startTransaction(false);
/*
		$sql = "UPDATE dev_app_task SET status=0 ".
               "WHERE taskid='$taskid' and os=$os and status!=6";
        VseLog::trace($sql);
        $this->_db->query($sql);
        $mtime = time();
        $sql = "UPDATE yz_mob_taskqueue SET status=0, mtime=$mtime ".
               "WHERE taskid='$taskid' and os=$os and status!=6";
        VseLog::trace($sql);
        $this->_db->query($sql);
*/
        ////////////////////////////////////////////////
        // IOS online包上传失败
		if ($os == 2) {
			$sql = "UPDATE dev_app_ios SET status=0 ".
				"WHERE taskid='$taskid' and status!=6";
			VseLog::trace($sql);
			$this->_db->query($sql);
			$sql = "UPDATE dev_ios_shelf set status = 5, reason = '打包失败' ".
				"WHERE taskid='$taskid' and status!=6";
			VseLog::trace($sql);
			$this->_db->query($sql);
        }
        ////////////////////////////////////////////////
		$this->_db->endTransaction(true);
        //////////////////////////////////////////////////////////
    }/*}}}*/

    // 上传完成后回调
    public function callback($taskid)
    {/*{{{*/
        $os = $this->_os;
        $sql = "SELECT callback FROM dev_app_task WHERE taskid='$taskid' and os=$os";
        $res = $this->_db->query($sql);
        if (!empty($res) && $res[0]["callback"]!="") {
			//print_r($res);
            $url = $res[0]["callback"];
            $res = $this->httpRequest($url);
            VseLog::trace("callback res: [$res]");
        } else {
            VseLog::trace("no callback defined [taskid:$taskid] [os:$os]");
            return;
        }
    }/*}}}*/

    // 发送http请求
    private function httpRequest($url ,$method = 'GET',$params = null) 
    {/*{{{*/
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if('POST' == $method){
			curl_setopt($ch, CURLOPT_POST, true);
			if(!empty($params)){
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			}       
		}else{  
			curl_setopt($ch, CURLOPT_HEADER, false); 
		}
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}/*}}}*/

}


function print_usage()
{
    global $argc, $argv;
    $exe = $argv[0];
    echo "usage: php $exe [product_code] [os_code]\n";
    echo "e.g.\n";
    echo "    php $exe 1 1  (upload Discuz Android Package)\n";
    echo "    php $exe 1 2  (upload Discuz IOS Package)\n";
    echo "    php $exe 2 1  (upload WordPress Android Package)\n";
    echo "    php $exe 2 2  (upload WordPress IOS Package)\n";
    exit(0);
}

if (__FILE__ == realpath($_SERVER['SCRIPT_FILENAME']))
{
    if ($argc < 3) {
        print_usage();
    }
    $product = intval($argv[1]);
    $os = intval($argv[2]);
    $parr = array(1,2);
    $oarr = array(1,2);
    if (!in_array($product, $parr) || !in_array($os, $oarr)) {
        print_usage();
    }

    $upload_worker = new UploadWorker($product, $os);
    $upload_worker->run();

/*
    $dataEngine = new DataEngine();
    $sql = "select * from dev_app_task limit 1";
    $s = $dataEngine->query($sql);
    print_r($s);
*/
}


// vim600: sw=4 ts=4 fdm=marker syn=php
?>
