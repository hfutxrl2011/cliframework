<?php
class Conf
{
	///////////////////////////////////////////////////////////////////////////////////
	//系统配置
	public static $version = '2.0.0.0'; //版本号 
	public static $env = 0;
	//日志配置
	public static $vseLogInfo = array(
			'file' => 'worker.log',
			'level' => 16,
			'type' => 'LOCAL_LOG',
			);
			
	public static $daemond = false;
	public static $packTask = 3;//可执行的任务类型
	public static $packTaskConf = array(
										"default"=>1,
										"IOS_Online"=>2,
									);//任务列表
									
	public static $fileServer = "";
	public static $fileServer4Os = "";
	
    public static $packOS = 1; //worker标识 1 android 2 IOS 可以自动适配
	
	public static $osMap = array("1"=>"ANDROID","2"=>"IOS");
	
	public static $packApp = 'DZ';//DZ discuz WP wordpress
	
	public static $packSvr = '10.3.40.7';//打包服务ip
	
	public static $expireTask = 900;//超时任务重新执行
	
	public static $packOverTimes = 5;//超时任务执行次数
	
	public static $status = array(
									"0"=>"失败",
									"1"=>"等待调度中",
									"2"=>"获取任务成功，开始打包",
									"3"=>"打包前准备，工程文件替换",
									"4"=>"打包中",
									"5"=>"打包成功，安装包拷贝，数据库更新",
									"6"=>"打包任务结束，打包流程完成",
							);
	public static $failedStatus = array(
									"0"=>"系统拒绝打包，应用发起的打包过多",
									"1"=>"调度失败",
									"2"=>"锁定任务失败",
									"3"=>"图片地址无法下载或者文件替换失败",
									"4"=>"打包编译失败",
									"5"=>"打包文件准备上传失败",
									"6"=>"文件上传失败",
							);
							
	public static $sourcedir = '/home/work/package/source';
	public static $sourcedir_wp = '/home/work/package/source_wp';
	
	public static $flowControl = array(
										"1"=>array(
												"app_id"=>array(),//1047
												"packsvr"=>array('127.0.0.1'),
											),
										"2"=>array(
												"app_id"=>array(),//1047
												"packsvr"=>array('127.0.0.1'),
											),	
								 );
	//打包脚本配置
	public static $packScript = array(
										'DZ_ANDROID'=>array(
														"script"=>'DZ_android.sh',
														"params"=>array("key1"=>"value1"),
														"appdir"=>"./data/Android/DZ/app",//应用apk目录
														"sourcedir"=>"./data/Android/DZ/source",//母包目录
														"pageagedir"=>"./data/Android/DZ/package",//打包源代码目录
														"tmpdir"=>"./data/Android/DZ/tmp",//打包源代码目录
										),
										'DZ_IOS'=>array(
														"script"=>'pack.sh',
														"script1"=>'cd /Users/youzu/Desktop/IOSPACKAGE/ && /bin/bash package_test.sh',
														"params"=>array(
																		"ouput_dir"=>"rl_dir",//1 name:文件夹名称 name="$1"
																		"app_name"=>"美女帮Club",//2 logopath:图标路径 appname="$2"
																		"icon_image"=>"/var/makepackage/packages/15401858133533168527_d7a4854de9e1885605289d15d9611ef0/download_image/icon.png",//3 logoname:图标名称 logopath="$3"
																		"startup_image"=>"/var/makepackage/packages/15401858133533168527_d7a4854de9e1885605289d15d9611ef0/download_image/recom.png",//4  launchImagePath="$4"
																		"bbs_name"=>"美女帮Club",//5 bbsName="$5"
																		"nav_color"=>"234,65,81",//6 navigationBarColor="$6"
																		"baseurl"=>"http://meinvbang.club",//7  baseurl="${7}"
																		"urlpath"=>"api/mobile/iyz_index.php",//8 urlpath="${8}"
																		"channel"=>"bigapp",//9  channel="${9}"
																		"app_key"=>"d7a4854de9e1885605289d15d9611ef0",//10 appkey="${10}"
																		"sharekey_sina"=>"2257958082",//11 sharekey_sina="${11}"
																		"shareSecret_sina"=>"61ec8c63481817f3c122af6c9d2596ff",//12 shareSecret_sina="${12}"
																		"shareRedirecturi_sina"=>"http://www.3body.com/",//13 shareRedirecturi_sina="${13}"
																		"shareKey_wechat"=>"wxc38fe19026b7591a",//14 shareKey_wechat="${14}"
																		"shareSecret_wechat"=>"f9e6050f7ab583e490d3745e1d2c607b",//15 shareSecret_wechat="${15}"
																		"sharekey_qq"=>"1104574417",//16 sharekey_qq="${16}"
																		"shareSecret_qq"=>"n00QoTcEUBPWqtHL",//17 shareSecret_qq="${17}"
																		"appversion"=>"1.0.1",// 18
																		"bundleid"=>"com.youzu.Clan",// 19
																		"jpush_appkey"=>"b7bf5ec527d73136c7a0944c",//20
																		//"jpush_appsecret"=>"2fec24cf15dc9125d145879e",//21
																		"env"=>"0",//21
																		"sourceAppPath"=>"2",//22
														),
														"appdir"=>"data/IOS/DZ/app",//应用apk目录
														"IOSPACKAGE"=>"/Users/youzu/Desktop/IOSPACKAGE",//母包目录
														"sourcedir"=>"data/IOS/DZ/source",//脚本+原始plist
														"packagedir"=>"data/IOS/DZ/package",//打包源代码目录
										),
										'WP_ANDROID'=>array(
														"script"=>'WP_android.sh',
														"params"=>array(),
														"appdir"=>"./data/Android/WP/app",//应用apk目录
														"sourcedir"=>"./data/Android/WP/source",//母包目录
														"packagedir"=>"./data/Android/WP/package",//打包源代码目录
														"tmpdir"=>"./data/Android/WP/tmp",//打包源代码目录
										),
										'WP_IOS'=>array(
														"script"=>'pack_online.sh',
														//sh ipa.sh "appKey" "appChanel" "博客002" "com.youzu.test" "1.0.0" "red" "http://www.youzu.com" "$(pwd)/icon.png" "$(pwd)/LaunchImag.png" "$(date +%Y-%m-%d_%H_%M)"
														"params"=>array(
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
														),
														"scriptdir"=>"/Users/youzu/Desktop/IOSPACKAGE/IOS_WP/WP_Package/ResignSrc/",
														"onlinescriptdir"=>"/Users/youzu/Desktop/IOSPACKAGE/IOS_WP/WP_Package/release/",
														"sourcedir"=>"data/IOS/WP/source",
														"packagedir"=>"data/IOS/WP/package",
														"appdir"=>"data/IOS/WP/app",
										),
	);
	
	//打包初始化
	public static $prePackScript = array(
										'DZ_ANDROID'=>array(
														"script"=>'DZ_android.sh',
														"params"=>array(),
										),
										'DZ_IOS'=>array(
														"script"=>'DZ_ios.sh',
														"params"=>array(),
										),
										'WP_ANDROID'=>array(
														"script"=>'WP_android.sh',
														"params"=>array(),
										),
										'DZ_IOS'=>array(
														"script"=>'WP_ios.sh',
														"params"=>array(),
										),
	);
	
	//打包拷贝设置
	public static $afterPackScript = array(
										'DZ_ANDROID'=>array(
														"script"=>'DZ_android.sh',
														"params"=>array(),
										),
										'DZ_IOS'=>array(
														"script"=>'DZ_ios.sh',
														"params"=>array(),
										),
										'WP_ANDROID'=>array(
														"script"=>'WP_android.sh',
														"params"=>array(),
										),
										'DZ_IOS'=>array(
														"script"=>'WP_ios.sh',
														"params"=>array(),
										),
	);
	
	///////////////////////////////////////////////////////////////////////////////////
	//数据库相关配置
	//重试次数
	public static $dbRetry = 3;
	//默认数据库获取批量
	public static $dbBatch = 1000;
	//数据库服务器配置信息
	public static $arrMysqlConf = array(
        'charset' => 'UTF8',
        'db_num' => 1,
        'tb_num' => 1,
        'server' => array(
            '0' => array(
                array(
                    'host' => '',
                    'port' => 3306,
                    'username' => '',
                    'password' => '',
                    'database'=> '',
                    ),
                ),
            ),
        );

	/////////////////////////////////////////////////////////////////////////////////////////
	//redis相关配置
	//redis重试次数
	public static $redisRetry = 3;
	//是否启用mset功能
	public static $enableRedisMuti = false;
	//缓存失效时间，25hour
	public static $redisExpire =90000;
	//redis服务器配置
	public static $arrRedis = array(
        'server' => array(
            "10.3.5.42",
        ),
        'connect_timeout' => 10,
        'port' => 6379,
    );

	/**
	 * 
	 * 错误映射和退出码映射
	 * @var array
	 */
	public static $arrErrorMap = array(
		'php' => 1,  //php错误导致程序退出
		'unknown' => 2, //未知错误导致程序退出
		'param' => 3, //参数错误导致程序退出
		'database' => 4,	//DB错误
		'redis' => 5, 	//redis错误
		'internal' => 6,	//内部错误，一般是错误的代码引起
		'task' => 7,//task 异常退出
	);
	
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	//DB导出字段相关的配置
	//与二进制相关的接口字段配置
	public static $dataSql = array(
        'yz_mob_taskqueue' => array(
		    //拉取一条符合条件的数据
            'sql' => 'SELECT taskid,os,app FROM yz_mob_taskqueue where (status = "1" and os = "2" and app = "DZ" ) order by ctime ASC limit 1', 
        ),
    );


    /////////////////////////////////////////////////////////
    // upload脚本使用的配置
    public static $uploadConf = array (
	    "upload_bin" => "rsync -avz --progress",
        //"upload_bin" => "rsync -vzr -e 'ssh -p 57522'",
        "save_work_path" => true,   //!< 是否保存工作目录
        "monitor_path" => array (
            "DZ_Android" => "data/Android/DZ/app",
            "DZ_IOS"     => "data/IOS/DZ/app",
            "WP_Android" => "data/Android/WP/app",
            "WP_IOS"     => "data/IOS/WP/app",
        ),
        "work_path" => array (
            "DZ_Android" => "data/Android/DZ/upload",
            "DZ_IOS"     => "data/IOS/DZ/upload",
            "WP_Android" => "data/Android/WP/upload",
            "WP_IOS"     => "data/IOS/WP/upload",
        ),
		"upload_root"    => "",
        "download_root"  => "",
    );

    /////////////////////////////////////////////////////////
	public static $defaultShareConfig = array(
			'key_alias' => 'youyu.keystore',
			'store_password' => '1422@youzu',
			'key_password' => 'xiaoshun@youzu',
			'key_store_content' => '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',
			'share_plat' => '[ { "flag": "0", "app_id": "wxb05003635a752c53", "sec_key": "1bf2e7110a83d2b1d2ce71c11f18ca36" },
			  { "flag": "0", "app_id": "1104768684", "sec_key": "QBWUohHz82fpd55h" },
			  { "flag": "0", "app_id": "3552514107", "sec_key": "b234a863f8cfb50e4ec7c47ca0babba9", "redirect_url_sina": "https://api.weibo.com/oauth2/default.html" } ]',
			);
}
?>
