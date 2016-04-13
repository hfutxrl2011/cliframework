<?php
/***************************************************************************
 * 
 * Copyright (c) 2011 Baidu.com, Inc. All Rights Reserved
 * $Id$ 
 * 
 **************************************************************************/

/**
 * @file bog.php
 * @date 2011/08/29 16:11:30
 * @version $Revision$ 
 * @brief 
 *  
 **/
require_once dirname(__FILE__) . '/BaeLog.class.php';
require_once('Bingo/Http/Ip.php'); 
require_once('Bingo/Log.php'); 


function yun_posix_getpid() {
	if ( function_exists('posix_getpid'))
		return posix_getpid();
	return 'unkown-posix-id';
}


final class __mc_log__ {
	const LOG_FATAL = 1;
	const LOG_WARNING = 2;
	const LOG_MONITOR = 3;
	const LOG_NOTICE = 4;
	const LOG_TRACE = 8;
	const LOG_DEBUG = 16;
	const PAGE_SIZE = 0;
	const LOG_SPACE = "\10";
	const MONTIR_STR = ' ---LOG_MONITOR---';
	const LOCAL_LOG = 'LOCAL_LOG';
	const NET_LOG = 'NET_LOG';
	
	private $request_log_level = self::LOG_DEBUG;
	private $yun_log_handler = NULL;
	private $VALID_LOG_TYPE = array (self::LOCAL_LOG, self::NET_LOG );
	private $LOG_TYPE = self::NET_LOG;

	static $LOG_NAME = array (
								self::LOG_FATAL => 'FATAL', 
								self::LOG_WARNING => 'WARNING', 
								self::LOG_MONITOR => 'MONITOR', 
								self::LOG_NOTICE => 'NOTICE', 
								self::LOG_TRACE => 'TRACE', 
								self::LOG_DEBUG => 'DEBUG' 
							  );
	static $BASIC_FIELD = array (
									'logid', 
									'ip', 
									'uid', 
									'uname', 
									'baiduid', 
									'method', 
									'uri' 
								 );
	
	private $log_name = '';
	private $log_path = '';
	private $wflog_path = '';
	private $log_str = '';
	private $wflog_str = '';
	private $basic_info = '';
	private $notice_str = '';
	private $log_level = self::LOG_DEBUG;
	private $arr_basic = NULL;
	private $force_flush = false;
	private $init_pid = 0;
	
	function __construct($logtype) {
		if (! in_array ( $logtype, $this->VALID_LOG_TYPE )) {
			throw new Exception ( 'invalid log type :' . print_r ( $this->VALID_LOG_TYPE, true ) );
		}
		$this->LOG_TYPE = $logtype;
	}
	
	function __destruct() {
		$this->check_flush_log ( true );
		if ($this->init_pid == yun_posix_getpid()) {
			$this->check_flush_log ( true );
		}
	}
	
	function init( $name, $level, $arr_basic_info, $flush = false) {
		$this->force_flush = $flush;
		if ($this->LOG_TYPE === self::LOCAL_LOG) {
			if ( empty ( $name )) {
				return false;
			}			
			$this->log_path =  $name ;
			$this->wflog_path = $name . ".wf";			
			$this->log_name = $name;
			$this->log_level = $level;
		} else if ($this->LOG_TYPE === self::NET_LOG) {
			require_once BAE_PATH. 'config/BaeLogConfigure.class.php';
			$this->yun_log_handler = BaeLog::getInstance ();
			$this->force_flush = true;
		}
		/* set basic info */
		$this->arr_basic = $arr_basic_info;
		/* 生成basic info的字符串 */
		$this->gen_basicinfo ();
		/* 记录初使化进程的id */
		$this->init_pid = yun_posix_getpid();
		return true;
	}
	
	private function gen_log_part($str) {
		return "[ " . self::LOG_SPACE . $str . " " . self::LOG_SPACE . "]";
	}
	
	private function gen_basicinfo() {
		$this->basic_info = '';
		foreach ( self::$BASIC_FIELD as $key ) {
			if (! empty ( $this->arr_basic [$key] )) {
				$this->basic_info .= $this->gen_log_part ( "$key:" . $this->arr_basic [$key] ) . " ";
			}
		}
	}
	
	public function check_flush_log($force_flush) {
		if (strlen ( $this->log_str ) > self::PAGE_SIZE || strlen ( $this->wflog_str ) > self::PAGE_SIZE) {
			$force_flush = true;
		}
		
		if ($force_flush) {
			/* first write warning log */
			if (! empty ( $this->wflog_str )) {
				$this->write_file ( $this->wflog_path, $this->wflog_str );
			}
			/* then common log */
			if (! empty ( $this->log_str )) {
				$this->write_file ( $this->log_path, $this->log_str );
			}
			/* clear the printed log*/
			$this->wflog_str = '';
			$this->log_str = '';
		} /* force_flush */
	}
	
	private function write_file($path, $str) {
		if ($this->LOG_TYPE === self::LOCAL_LOG) {
		$fd = @fopen ( $path, "a+" );
			if (is_resource ( $fd )) {
				fputs ( $fd, $str );
				fclose ( $fd );
			} else {
				trigger_error ( "cant open log path:$path", E_USER_WARNING );
			}
		} else if ($this->LOG_TYPE === self::NET_LOG) {
			switch ($this->request_log_level) {
				case self::LOG_FATAL :
					{
						$this->yun_log_handler->logFatal ( $str );
						break;
					}
				case self::LOG_DEBUG :
					{
						$this->yun_log_handler->logDebug ( $str );
						break;
					}
				case self::LOG_TRACE :
					{
						$this->yun_log_handler->logTrace ( $str );
						break;
					}
				case self::LOG_WARNING :
					{
						$this->yun_log_handler->logWarning ( $str );
						break;
					}
				case self::LOG_NOTICE :
					{
						$this->yun_log_handler->logNotice ( $str );
						break;
					}
				default :
					{
						trigger_error ( "unknown log level", E_USER_WARNING );
					}
			
			}
		} else {
			trigger_error ( "invalid log type", E_USER_WARNING );
		}
	}
	
	public function add_basicinfo($arr_basic_info) {
		$this->arr_basic = array_merge ( $this->arr_basic, $arr_basic_info );
		$this->gen_basicinfo ();
	}
	
	public function push_notice($format, $arr_data) {
		$this->notice_str .= " " . $this->gen_log_part ( vsprintf ( $format, $arr_data ) );
	}
	
	public function clear_notice() {
		$this->notice_str = '';
	}
	
	public function write_log($type, $format, $line_no, $arr_data) {
		if ($this->log_level < $type)
			return;		
		$this->request_log_level = $type;

		if ($this->LOG_TYPE === self::LOCAL_LOG) {
			$str = sprintf ( "%s: %s * %d %s", self::$LOG_NAME [$type], date ( "m-d H:i:s" ),  yun_posix_getpid(), $line_no );
		} else if ($this->LOG_TYPE === self::NET_LOG) {
			$str = sprintf ( " %s * %d %s", date ( "m-d H:i:s" ),  yun_posix_getpid(), $line_no );
		}


		
		
		/* add monitor tag?	*/
		if ($type == self::LOG_MONITOR || $type == self::LOG_FATAL) {
			$str .= self::MONTIR_STR;
		}
		/* add basic log */
		$str .= " " . $this->basic_info;
		
		/* add detail log */
		if (empty ( $arr_data )) {
			$str .= $format;
		} else {
			$str .= " " . vsprintf ( $format, $arr_data );
		}
		
		switch ($type) {
			case self::LOG_MONITOR :
			case self::LOG_FATAL :
			case self::LOG_WARNING :
			case self::LOG_FATAL :
				$this->wflog_str .= $str . "\n";
				break;
			case self::LOG_DEBUG :
			case self::LOG_TRACE :
				$this->log_str .= $str . "\n";
				break;
			case self::LOG_NOTICE :
				$this->log_str .= $str . $this->notice_str . "\n";
				$this->clear_notice ();
				break;
			default :
				break;
		}
		$this->check_flush_log ( $this->force_flush );
	}
}


class VseLog{

	private static $__log__ = null;
	private static function _ub_log($type, $arr) {
		
		$format = $arr[0];
		array_shift($arr);
		$pid = yun_posix_getpid();
		$bt = debug_backtrace ();
		if (isset ( $bt [1] ) && isset ( $bt [1] ['file'] )) {
			$c = $bt [1];
		} else if (isset ( $bt [2] ) && isset ( $bt [2] ['file'] )) { //为了兼容回调函数使用log
			$c = $bt [2];
		} else if (isset ( $bt [0] ) && isset ( $bt [0] ['file'] )) {
			$c = $bt [0];
		} else {
			$c = array ('file' => 'faint', 'line' => 'faint' );
		}
		$pwd = getcwd();
		$fPath = substr($c['file'], strlen($pwd));
		$c['file'] = trim($fPath, '/');
		$line_no = '[' . $c ['file'] . ':' . $c ['line'] . '] ';
		if (!empty(self::$__log__[$pid])) {
			$log = self::$__log__[$pid];
			$log->write_log($type, $format, $line_no, $arr);
		} else {
			$s =  __mc_log__::$LOG_NAME[$type] . ' ' . vsprintf($format, $arr) . "\n";
			echo $s;
		}
	}


	private  static  function _init($logtype ,$level, $info,  $file = null,$flush=false) {
	
		$pid = yun_posix_getpid();
		if (!empty(self::$__log__[$pid]) ) {
			unset(self::$__log__[$pid]);
		}
		self::$__log__[yun_posix_getpid()] = new __mc_log__($logtype);
		$log = self::$__log__[yun_posix_getpid()];
		if ($log->init( $file, $level, $info, $flush)) {
			return true;
		} else {
			unset(self::$__log__[$pid]);
			return false;
		}
	}


	public static function init($arrConf) {
		$logFile = $arrConf['file'];
		$logLevel = $arrConf['level'];
		$logType = $arrConf['type'];
		if("LOCAL_LOG"== $logType) {
			if(is_file(dirname($logFile)))
			{
				throw new Exception(__FUNCTION__ . ' failed, a regular file named [ ' . dirname($logFile) .' ] already exists, cannot create log dir');
			}
			if(!file_exists(dirname($logFile)))
			{
				$oldMask = umask(0000);
				$mkdirRet = @mkdir(dirname($logFile), 0775, true);
				umask($oldMask);
				if(false === $mkdirRet)
				{
					throw new Exception(__FUNCTION__ . ' failed, cannot make log directory [ logFile: ' . $logFile . ' ].');
				}
			}
		 	self::_init($logType, $logLevel, array(),  $logFile);
		}else {
            		self::_init($logType, $logLevel, array());
		}
	}


	

	public static function  debug() {
		$arg = func_get_args();
		self::_ub_log(__mc_log__::LOG_DEBUG, $arg );
	}
	public static function  trace() {
		$arg = func_get_args();
		self::_ub_log(__mc_log__::LOG_TRACE, $arg );
	}
	public static function  notice() {
		$arg = func_get_args();
		self::_ub_log(__mc_log__::LOG_NOTICE, $arg );
	}
	public static function  monitor() {
		$arg = func_get_args();
		self::_ub_log(__mc_log__::LOG_MONITOR, $arg );
	}
	public static function  warning() {
		$arg = func_get_args();
		self::_ub_log(__mc_log__::LOG_WARNING, $arg );
	}
	public static function  fatal() {
		$arg = func_get_args();
		self::_ub_log(__mc_log__::LOG_FATAL, $arg );
	}


	public static function pushNotice() {
	
		$arr = func_get_args();
		$pid = yun_posix_getpid();
		if (!empty(self::$__log__[$pid])) {
			$log = self::$__log__[$pid];
			$format = $arr[0];
			/* shift $type and $format, arr_data left */
			array_shift($arr);
			$log->push_notice($format, $arr);
		} else {
		/* nothing to do */
		}
	}

	public static function clearNotice() {
	
		$pid = yun_posix_getpid();
		if (!empty(self::$__log__[$pid])) {
			$log = self::$__log__[$pid];
			$log->clear_notice();
		} else {
		/* nothing to do */
		}
	}

	public static function addBasic($arr_basic) {
	
		$pid = yun_posix_getpid();
		if (!empty(self::$__log__[$pid])) {
			$log = self::$__log__[$pid];
			$log->add_basicinfo($arr_basic);
		} else {
			/* nothing to do */
		}
	}

	public static function flushLog() {
		
		$pid = yun_posix_getpid();
		if (! empty ( self::$__log__ [$pid] )) {
			$log = self::$__log__ [$pid];
			$log->check_flush_log ( true );
		} else {
		/* nothing to do */
		}
	}

}




/* vim: set ts=4 sw=4 sts=4 tw=100 noet: */
?>
