<?php
class MysqlManager 
{
	protected $_mysqli = NULL;
	protected $_isConnected = false;
	
	public function __construct() 
	{
        $this->_mysqli = mysqli_init();
    }

    public function __destruct() 
	{
    	if($this->_isConnected) 
		{
       		$this->_mysqli->close();
    	}
    }

	public static function getTbNo($appId)
	{
		$sign = $appId;
		is_numeric($appId) || $sign = Sign::sign64($appId);
		$tag = (Sign::mod($sign, (Conf::$arrMysqlConf['db_num'] * Conf::$arrMysqlConf['tb_num']))) % Conf::$arrMysqlConf['tb_num'];
		return $tag;
	}

    /**
     * 
     * 获得数据库编号，目前只有一个库
     * @param unknown_type $appId
     */
	public static function getDbNo($appId)
	{
		$sign = $appId;
		is_numeric($appId) || $sign = Sign::sign64($appId);
		$tag = intval((Sign::mod($sign, (Conf::$arrMysqlConf['db_num'] * Conf::$arrMysqlConf['tb_num']))) / Conf::$arrMysqlConf['tb_num']);	
		return $tag;
	}

	/**
	 * 
	 * 开启一个事务
	 */
	public function startTransaction()
	{
		if(false === $this->query('START TRANSACTION'))
		{
			VseLog::warning(__FUNCTION__ . " failed because execute execute sql failed.");
			return false;
		}
		VseLog::debug(__FUNCTION__ . " succ.");
		return true;
	}

	/**
	 * 
	 * 结束一个事务
	 * @param boolean $commit 是否提交事务
	 */
	public function endTransaction($commit = false)
	{
		$sql = 'ROLLBACK';
		if($commit)
		{
			$sql = 'COMMIT';
		}
		if(false === $this->query($sql))
        {
            VseLog::warning(__FUNCTION__ . " failed because execute execute sql failed.");
            return false;
        }
        VseLog::debug(__FUNCTION__ . " succ.");
        return true;	
	}

	public function getError()
	{
		if(!$this->_isConnected)
        {
            VseLog::warning(__FUNCTION__ . " failed, db has not been connected [ sql: $sql ]") ;
            return false;
        }
		return array(
			'errno' => $this->_mysqli->errno,
			'errmsg' => $this->_mysqli->error,
		);	
	}
	
	/**
	 * 
	 * 获得上次数据库访问影响到的行数
	 */
	public function getAffectRows()
	{
		if(!$this->_isConnected)
        {
            VseLog::warning(__FUNCTION__ . " failed, db has not been connected [ sql: $sql ]") ;
            return false;
        }
		return $this->_mysqli->affected_rows;	
	}

	/**
	 * 
	 * 执行一条SQL语句
	 * @param string $sql 要执行的SQL语句
	 */
	public function query($sql)
	{
		$start = intval(microtime(true) * 1000);
		if(!$this->_isConnected)
		{
			VseLog::warning(__FUNCTION__ . " failed, db has not been connected [ sql: $sql ]")	;
			return false;
		}
        $result = $this->_mysqli->query($sql);
		$end = intval(microtime(true) * 1000);
		$cost = $end - $start;
        if(false === $result &&  $this->_mysqli->errno )
        {
            VseLog::warning("execute sql failed [ sql: ${sql}, error_info: " . $this->_mysqli->error . ', error_no: ' . $this->_mysqli->errno . ' ].');
            return false;
        }

        if ( true === $result )
        {
            VseLog::trace ( "execute sql succ [ sql: ${sql}, ret: 'true', cost: $cost ms ]." );
            return $result;
        }

        $ret = array();
        while($row = $result->fetch_assoc())
        {
            array_push($ret, $row);
        }
        $result->close();
		VseLog::trace ( "execute sql succ [ sql: ${sql}, count: " . count($ret) . ", cost: $cost ms ]." );
        return $ret;	
	}

	/**
	 * 
	 * 连接到数据库
	 * @param string $appId 连接到哪个数据库，分表依据
	 * @param string $tag 直接指明连接到哪个数据库
	 */
	public function fetchMysqlHandler($appId, $tag = NULL) 
	{
		if($this->_isConnected) 
		{
			$this->_mysqli->close();
       		$this->_isConnected = false;
       		$this->_mysqli = mysqli_init();
    	}
		if(!is_null($tag)) 
		{
			if(!isset(Conf::$arrMysqlConf['server'][intval($tag)]))
			{
				VseLog::warning(__FUNCTION__ . " failed, invalid tag received [ tag: $tag ]");
				return false;
			}
			$arrMysqlServer = Conf::$arrMysqlConf['server'][intval($tag)];
		}
		else 
		{
			$tag = $this->getDbNo($appId);
			$arrMysqlServer = Conf::$arrMysqlConf['server'][intval($tag)];
		}
		$totalNum = count($arrMysqlServer);
		$index = mt_rand(0, $totalNum-1);
		for($i = 0; $i < $totalNum; $i++) 
		{
			$mysqlServer = $arrMysqlServer[$index];
			if(!isset($mysqlServer['host']) || !isset($mysqlServer['username']) || 
				!isset($mysqlServer['password']) || !isset($mysqlServer['database']) || 
				!isset($mysqlServer['port']))
			{
				VseLog::warning(__FUNCTION__ . " failed, config must have host/username/password/database/port fields [mysqlServer: " . json_encode($mysqlServer) . "]");
				return false;
			}
			if(false === @$this->_mysqli->real_connect(
													$mysqlServer['host'], 
													$mysqlServer['username'], 
													$mysqlServer['password'], 
													$mysqlServer['database'], 
													$mysqlServer['port'], 
													NULL, 
													0
													)) 
			{
				$index = (++$index % $totalNum);
				continue;
			}
			VseLog::debug("fetch mysql conntion host [" . $mysqlServer['host'] . "] port [" . $mysqlServer['port'] . "]");
			$this->_mysqli->set_charset(Conf::$arrMysqlConf['charset']);
			$this->_mysqli->ping();
			$this->_isConnected = true;
			break;
		}
		if(false === $this->_isConnected) 
		{
			VseLog::warning("fetch mysql conntion [appId: $appId, tag: $tag] failed.");
			return false;
		}
		return true;
	}
	
	/**
	 * 
	 * 转义一个字符串
	 * @param string $strVal 要转义的字符串
	 */
	public function escapeString($strVal)
	{
		if(!$this->_isConnected)
		{
			VseLog::warning(__FUNCTION__ . " failed, db has not been connected [ strVal: $strVal ].")	;
			return false;
		}
		return $this->_mysqli->escape_string($strVal);
	}
	
	/**
	 * 
	 * 批量转义字符串
	 * @param array $arrFields 要转义的数组
	 * @param array $arrStrFields 哪些KEY是字符串类型的
	 */
	public static function escapeStrings(&$arrFields, $arrStrFields = null)
	{
		$db = new MysqlManager();
        if(false === $db->fetchMysqlHandler(null, 0))
        {   
            VseLog::waning(__FUNCTION__ . ' failed, fetch mysql handler failed [ dbNo: 0, arrFields: ' . json_encode($arrFields) . ', arrStrFields: ' . json_encode($arrStrFields)  . ' ].');
			return false;
        }
		foreach($arrFields as $k => &$v)
		{
			if(is_null($arrStrFields))
			{
				(!is_null($v)) && $v = "'" . $db->escapeString($v) . "'";
				continue;
			}
			in_array($k, $arrStrFields) && (!is_null($v)) && $v = "'" . $db->escapeString($v) . "'";
		}
		return true;
	}
}
?>
