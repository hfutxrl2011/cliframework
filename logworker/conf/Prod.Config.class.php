<?php
class Conf
{
	///////////////////////////////////////////////////////////////////////////////////
	//系统配置
	public static $version = '2.0.0.0'; //版本号 
	//日志配置
	public static $vseLogInfo = array(
			'file' => 'worker.log',
			'level' => 16,
			'type' => 'LOCAL_LOG',
			);
			
	public static $daemond = false;
	
	public static $logType = array(
								   "1"=>"LogProcess",//日志数据
								   "2"=>"PackageProcess",//IOS应用包
								   "4"=>"PackageBakProcess",//IOS worker 应用包数据
								   "8"=>"UploadProcess",// uploader 数据
								   "16"=>"FilesProcess",//文件服务器数据
							 );
    public static $taskList = array("0");//执行所有类型日志任务
	
	public static $logRule = array(
							       "1"=>array(
											array(
											 "os"=>1,
											 "path"=>"/data/web/app/ui/log/",
											 "filename"=>array('mui.api.log','mui.uc.log','worker.log'),
											 "cuttime"=>3600,//备份时间24*60*60
											 "backuptime"=>86400,//备份时间24*60*60
											 "bak_path"=>"/data/web/app/ui/log/",
											 "rmtime"=>604800,//删除时间7*24*60*60
											),
											array(
											 "os"=>1,
											 "path"=>"/home/work/packworker/log/",
											 "filename"=>array('script.log','uploader.log','worker.log'),
											 "cuttime"=>3600,//备份时间24*60*60
											 "backuptime"=>86400,//备份时间24*60*60
											 "bak_path"=>"/home/work/packworker/log/bak/",
											 "rmtime"=>604800,//删除时间7*24*60*60
											),
											array(
											 "os"=>1,
											 "path"=>"/home/work/packworker2/log/",
											 "filename"=>array('script.log','uploader.log','worker.log'),
											 "cuttime"=>3600,//备份时间24*60*60
											 "backuptime"=>86400,//备份时间24*60*60
											 "bak_path"=>"/home/work/packworker2/log/bak/",
											 "rmtime"=>604800,//删除时间7*24*60*60
											),
											array(
											 "os"=>2,
											 "path"=>"/Users/home/work/packworker/log/",
											 "filename"=>array('script.log','uploader.log','worker.log'),
											 "cuttime"=>3600,//h
											 "backuptime"=>86400,//备份时间
											 "bak_path"=>"/Users/home/work/packworker/log/bak/",//Ymd
											 "rmtime"=>604800,//删除时间7*24*60*60
											),
											array(
											 "os"=>2,
											 "path"=>"/Users/home/work/test_packworker/log/",
											 "filename"=>array('script.log','uploader.log','worker.log'),
											 "cuttime"=>3600,//h
											 "backuptime"=>86400,//备份时间
											 "bak_path"=>"/Users/home/work/test_packworker/log/bak/",//Ymd
											 "rmtime"=>604800,//删除时间7*24*60*60
											),
									),
									"2"=>array(
											array(
											 "os"=>2,
											 "path"=>"/Users/home/Desktop/IOSPACKAGE/package/",
											 "filename"=>array('*'),
											 "rmtime"=>604800,//删除时间7*24*60*60
											),
											array(
											 "os"=>2,
											 "path"=>"/Users/home/Desktop/IOSPACKAGE/package/",
											 "filename"=>array('*'),
											 "rmtime"=>604800,//删除时间7*24*60*60
											),
									),
									"4"=>array(
											array(
											 "os"=>2,
											 "path"=>"/Users/home/work/packworker/data/IOS/DZ/package/",
											 "filename"=>array('*'),
											 "rmtime"=>604800,//604800,//删除时间7*24*60*60
											),
											array(
											 "os"=>2,
											 "path"=>"/Users/home/work/test_packworker/data/IOS/DZ/package/",
											 "filename"=>array('*.bak'),
											 "rmtime"=>3600,//for test //604800,//删除时间7*24*60*60
											),
											array(
											 "os"=>1,
											 "path"=>"/home/work/packworker/data/Android/DZ/package/",
											 "filename"=>array('*'),
											 "rmtime"=>604800,//删除时间7*24*60*60
											),
											array(
											 "os"=>1,
											 "path"=>"/home/work/packworker2/data/Android/DZ/package/",
											 "filename"=>array('*'),
											 "rmtime"=>604800,//删除时间7*24*60*60
											),
									),
									"8"=>array(
											array(
											 "os"=>2,
											 "path"=>"/Users/home/work/packworker/data/IOS/DZ/upload/",
											 "filename"=>array('*'),
											 "rmtime"=>604800,//删除时间7*24*60*60
											),
											array(
											 "os"=>2,
											 "path"=>"/Users/home/work/test_packworker/data/IOS/DZ/upload/",
											 "filename"=>array('*'),
											 "rmtime"=>3600,//删除时间7*24*60*60
											),
											array(
											 "os"=>1,
											 "path"=>"/home/work/packworker/data/Android/DZ/upload/",
											 "filename"=>array('*'),
											 "rmtime"=>604800,//删除时间7*24*60*60
											),
											array(
											 "os"=>1,
											 "path"=>"/home/work/packworker2/data/Android/DZ/upload/",
											 "filename"=>array('*'),
											 "rmtime"=>604800,//删除时间7*24*60*60
											),
									),
									"16"=>array(
											array(
											 "os"=>1,
											 "path"=>"/var/www/packageinterface/IOS_TAR/",
											 "filename"=>array('*.tar.gz'),
											 "rmtime"=>2592000,//删除时间7*24*60*60
											),
											array(
											 "os"=>1,
											 "path"=>"/var/www/packageinterface/IOS/",
											 "filename"=>array('*.ipa'),
											 "rmtime"=>2592000,//删除时间30*24*60*60 IOS ipa保留一个月
											),
											array(
											 "os"=>1,
											 "path"=>"/var/www/packageinterface/APK_TAR/",
											 "filename"=>array('*.tar.gz'),
											 "rmtime"=>2592000,//删除时间7*24*60*60
											),
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
            "",
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

}
?>
