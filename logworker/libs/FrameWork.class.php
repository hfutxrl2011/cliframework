<?php
/* *****************************************************************************/
/**
* @Brief 框架类
*/
/* *****************************************************************************/
class FrameWork
{
	public function __construct()
	{
	}
	
	public function __destruct()
	{
	}
	/**
	 * 
	 * 初始化函数，完成异常、错误函数的注册
	 */
	public function init()
	{
		date_default_timezone_set("Asia/Shanghai");
		Conf::$vseLogInfo['file'] = dirname(dirname(__FILE__)) . '/log/' . Conf::$vseLogInfo['file'];
		VseLog::init(Conf::$vseLogInfo);	
        error_reporting(E_ALL | E_STRICT);
        set_error_handler(array($this,'errorHandler'));
        set_exception_handler(array($this,'exceptionHandler'));
		VseLog::trace(__FUNCTION__ . ' succ.');
	}

	/**
	 * 
	 * 运行函数
	 */
	public function run()
	{
		do{
			$startStr = '--[ SCRIPT RUN START ]--';
			$strEnd = '--[ SCRIPT RUN END ]--';
			$start = intval(microtime(true)* 1000);
			VseLog::notice($startStr . ' [ time: ' . date("Y-m-d H:i:s", intval($start / 1000)) . ' ].');
			try {    
				echo "start working....".PHP_EOL;
				$logic = new MainLogic();
				$logic->run();
				echo "done one task....".PHP_EOL;
				$sec = 600;
				echo "sleep $sec s....".PHP_EOL;
				sleep($sec);
				$end = intval(microtime(true) * 1000);
				VseLog::notice($strEnd . ' [ time: ' . date("Y-m-d H:i:s", intval($end / 1000)) . ', time elapse: ' . ($end - $start) . ' ms ].');
			} catch (Exception $e) {
				$exitCode = $this->_exceptionHandler($e);
				if(Conf::$daemond){
					if($exitCode == 7){
						$sec = rand(1,10);
						VseLog::notice("do not do this task,sleep $sec s...");
						$end = intval(microtime(true) * 1000);
						VseLog::notice($strEnd . ' [ time: ' . date("Y-m-d H:i:s", intval($end / 1000)) . ', time elapse: ' . ($end - $start) . ' ms ].');
						echo "do nothing,continue....".PHP_EOL;
						sleep($sec);
						continue;
					}else{
						VseLog::warning('unkown error,but continue...error_code:' . $exitCode);
						//continue;
						exit($exitCode);
					}
				}else{
					VseLog::warning('run end...error_code:' . $exitCode);
				}
			} 
		}while(Conf::$daemond);
	}

	/**
	 * 
	 * 错误处理函数
	 */
	private function _errorHandler() 
	{
		restore_error_handler();
		$error = func_get_args();
		$errStr = sprintf("errno: %d, errmsg: %s, file: %s, line: %d", $error[0], $error[1], $error[2], $error[3]);
		if (!($error[0] & error_reporting())) {
			VseLog::trace('php caught info, ' . $errStr);
			set_error_handler(array($this,'errorHandler'));
			return false;
		} elseif ($error[0] === E_USER_NOTICE) {
			VseLog::trace('php caught notice, ' . $errStr);
			set_error_handler(array($this,'errorHandler'));
			return false;
		} elseif($error[0] === E_STRICT) {
			VseLog::trace('php caught strict, ' . $errStr);
			set_error_handler(array($this, 'errorHandler'));
			return false;
		} else {
			VseLog::warning('php caught error, ' . $errStr);
			return true;
		}
	}
	
	/**
	 * 
	 * 错误处理函数 
	 */
	public function errorHandler() 
	{
        $error = func_get_args();
        if (false === $this->_errorHandler($error[0], $error[1], $error[2], $error[3])) 
		{
			VseLog::warning('debuging...' . json_encode($error));
            return;
        }
        exit(1);
    }	

    /**
     * 
     * 异常处理函数
     * @param object $ex 异常对象
     */
	private function _exceptionHandler($ex) 
	{
        restore_exception_handler();
        $errCode = $ex->getMessage();
        if (0 < ($pos = strpos($errCode,' '))) {
			$errInfo = substr($errCode, $pos + 1);
            $errCode = substr($errCode, 0, $pos);
        }
        if(!array_key_exists($errCode, Conf::$arrErrorMap))
        {
			$errInfo = 'unknown';
            $errCode = 'unknown';
        }
		$errmsg = sprintf('Exception was caught, [ errCode:%s, errInfo: %s ], trace: %s', $errCode, $errInfo, $ex->__toString());
        if(Conf::$arrErrorMap[$errCode] == 7){
			VseLog::trace("do not care, trace info: [ errCode:$errCode, errInfo: $errInfo ]");
		}else{
			VseLog::fatal($errmsg);
		}
        return Conf::$arrErrorMap[$errCode];
    }
	
   	/**
   	 * 
   	 * 异常处理函数
   	 * @param object $ex 异常对象
   	 */
	public function exceptionHandler($ex) 
	{
        $exitCode = $this->_exceptionHandler($ex);
        exit($exitCode);
    }
}

?>
