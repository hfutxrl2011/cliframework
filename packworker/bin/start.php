<?php
define('ROOT_PATH' , dirname(dirname(__FILE__)));
define('LIB_PATH' , ROOT_PATH."/libs/");
define('CONF_PATH' , ROOT_PATH."/conf/");
set_include_path( LIB_PATH. PATH_SEPARATOR .get_include_path() );
define('WORKER_ENV' , 'Prod');
require_once CONF_PATH . WORKER_ENV.'.Config.class.php';
require_once CONF_PATH . 'Client.class.php';
require_once 'Log/Log.php';
require_once 'FrameWork.class.php';
require_once 'Logic/BaseProcess.php';
require_once 'Logic/DZIOSProcess.php';
require_once 'Logic/NewDZIOSProcess.php';
require_once 'Logic/WPIOSProcess.php';
require_once 'Logic/DZANDROIDProcess.php';
require_once 'Logic/WPANDROIDProcess.php';
require_once 'Logic/Package.class.php';
require_once 'MainLogic.class.php';
require_once 'Utils/FileWrapper.class.php';
require_once 'Utils/Sign.class.php';
require_once 'Utils/Timer.class.php';
require_once 'Utils/Utils.class.php';
require_once 'DataEngine/DataEngine.class.php';
require_once 'DataEngine/IOSEngine.class.php';
require_once 'Logic/OnlineIOSProcess.php';

/**
 * 
 * 入口函数
 */
function entry()
{
	$frameWork = new FrameWork();
	$frameWork->init();
	$frameWork->run();
}

/**
 * 
 * 帮助函数
 */
function printUsage()
{
	echo "**************************************************************\n";
	echo "Usage:\n\tphp " . basename(__FILE__) . " -shortOptions\n";
	echo "\t" . " -h show this message\n";
	echo "\t" . " -v print version\n";
	echo "\t" . " -d do daemod process\n";
	echo "**************************************************************\n\n";
	exit(0);
}

/**
 * 
 * 版本函数
 */
function printVersion()
{
	echo "***********************************\n";
	echo "module:\t\t" . basename(__FILE__) . "\n";
	echo "version:\t" . Conf::$version . "\n";
	echo "author:\t\tmobile@youzu.com\n";
	echo "***********************************\n\n";
	exit(0);
}

$opts = 'hvd';
$arrOptions = getopt($opts);
if(isset($arrOptions['h']))
{   
	printUsage();
}
if(isset($arrOptions['v']))
{   
	printVersion();
}

if(isset($arrOptions['d']))
{   
	Conf::$daemond = true;
}
if (__FILE__ == realpath($_SERVER['SCRIPT_FILENAME']))
{
	entry();
}	

?>
