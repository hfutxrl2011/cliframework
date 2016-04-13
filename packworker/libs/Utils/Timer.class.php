<?php
class Timer
{
	protected static $_arrTime = array();
	public static function setStart($key)
	{
		self::$_arrTime[$key][0] = intval(microtime(true) * 1000);
	}

	public static function setEnd($key){
		self::$_arrTime[$key][1] = intval(microtime(true) * 1000);
	}

	public static function getCost($key, $erase = true){
		$end = $start = null;
		if(isset(self::$_arrTime[$key][1])){
			$end = self::$_arrTime[$key][1];
		}
		if(isset(self::$_arrTime[$key][0])){
			$start = self::$_arrTime[$key][0];
		}
		if(isset(self::$_arrTime[$key]) && true === $erase){
			unset(self::$_arrTime[$key]);
		}
		if(!is_null($start) && !is_null($end) && $end >= $start){
			return $end - $start;
		}
		return 0;
	}

	protected static function getLogStr($arr)
	{
		$key = $arr[0];
		$fmt = $arr[1];
		array_shift($arr);
		array_shift($arr);
		if(isset($arr) && !empty($arr)){
			$logStr = vsprintf($fmt, $arr);
		}else{
			$logStr = $fmt;
		}
		$logStr = trim($logStr, '.');
		$cost = self::getCost($key);
		$logStr .= " [ $key TIME COST: $cost ms].";
		return $logStr;
	}

	public static function TRACE()
	{
		$logStr = self::getLogStr(func_get_args());
		call_user_func('VseLog::' . __FUNCTION__, $logStr);
	}
	
	public static function DEBUG()
	{
		$logStr = self::getLogStr(func_get_args());
		call_user_func('VseLog::' . __FUNCTION__, $logStr);
	}
	
	public static function NOTICE()
	{
		$logStr = self::getLogStr(func_get_args());
		call_user_func('VseLog::' . __FUNCTION__, $logStr);
	}

	public static function WARNING()
	{
		$logStr = self::getLogStr(func_get_args());
		call_user_func('VseLog::' . __FUNCTION__, $logStr);
	}
}
?>
