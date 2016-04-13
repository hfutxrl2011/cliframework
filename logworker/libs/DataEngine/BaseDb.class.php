<?php

require_once (dirname(__FILE__) . '/MysqlManager.class.php');

class BaseDb
{
	private $_dbObj = null;
	private $_inTrans = false;
	public function getConnection($throw = true, $force = false)
	{
		if(true === !!$force){
			$this->freeConnection();
		}
		if(!is_null($this->_dbObj)){
			return $this->_dbObj;
		}
		$this->_dbObj = new MysqlManager();
		if(false === $this->_dbObj->fetchMysqlHandler(0, 0))
		{
			$tag = 0;
			$errInfo = $this->_dbObj->getError();
			$this->freeConnection();
			if(true === !!$throw){
				throw new Exception('database ' . __FUNCTION__ . 
						" failed, fetch mysql hander failed [ db no: $tag, mysql error: " . $errInfo['errmsg'] . ' ].');
			}else{
				VseLog::warning('%s failed, fetch mysql hander failed [ db no: %s, mysql error: %s ].', __FUNCTION__, $tag, $errInfo['errmsg']);
			}
		}	
	}

	protected function freeConnection($commitTransaction = false){
		if(!is_null($this->_dbObj)){
			if(true === $this->_inTrans){
				$this->_dbObj->endTransaction($commitTransaction);
				$this->_inTrans = false;
			}
			unset($this->_dbObj);
			$this->_dbObj = null;
		}
	}
	
	public function startTransaction($commitLast = false, $throw = true){
		if(true === $this->_inTrans){
			$this->_dbObj->endTransaction($commitLast);
			$this->_inTrans = false;
		}
		$ret = $this->_dbObj->startTransaction();
		if(false === $ret && true === $throw){
			throw new Exception('database ' . __FUNCTION__ . ' failed, start transaction failed [ commit last: ' . $commitLast . ' ].');
		}
		if(true === $ret){
			$this->_inTrans = true;
		}
		return $ret;
	}
	
	public function endTransaction($commit = false, $throw = true){
		$ret = true;
		if(true === $this->_inTrans){
			$ret = $this->_dbObj->endTransaction($commit);
			if(false === $ret && true === $throw){
				throw new Exception(__FUNCTION__ . ' failed, end transaction failed [ commit: ' . $commit . ' ].');
			}
		}
		return $ret;
	}

	public function query($sql, $throw = true)
    {
        $ret = false;
        $retry = 0;
		$this->getConnection($throw, false);
        while($retry++ < Conf::$dbRetry){
            $ret = $this->_dbObj->query($sql);
            if(false !== $ret){
                //成功，直接返回
                return $ret;
            }
            $errInfo = $this->_dbObj->getError();
            if(1062 === intval($errInfo['errno'])){
                //执行成功，明确返回key冲突，直接返回
                if(true === !!$throw){
                    throw new Exception(__FUNCTION__ . " failed, execute sql failed [ sql: $sql, mysql error: " . $errInfo['errmsg'] . ' ].');
                }
                VseLog::warning(__FUNCTION__ . " failed, execute sql failed [ sql: $sql, mysql error: " . $errInfo['errmsg'] . ' ].');
                return false;
            }
            VseLog::warning(__FUNCTION__ . " failed, execute sql failed, retry " .
                    "[ sql: $sql, mysql error: " . $errInfo['errmsg'] . ', retry: ' . $retry . ' ].');
            sleep(1);
			$this->getConnection($throw, true);
        }

        VseLog::warning("query database all failed [ sql: $sql ].");
        if(true === !!$throw){
            throw new Exception(__FUNCTION__ . " failed, all retry failed [ sql: $sql ].");
        }
        VseLog::warning(__FUNCTION__ . " failed, all retry failed [ sql: $sql ].");
        return false;
    }
	
	
	protected function getAffectRows($throw = true){
		if(is_null($this->_dbObj)){
			if(true === $throw){
				throw new Exception(__FUNCTION__ . ' failed, object has not been init yet');
			}else{
				return false;
			}
		}
		$ret = $this->_dbObj->getAffectRows();
		if(false === $ret && true === $throw){
			$errInfo = $this->_dbObj->getError();
			throw new Exception('database ' . __FUNCTION__ . ' failed, get affect rows failed [ error info: ' . $errInfo['errmsg'] . ' ].');
		}
		return $ret;
	}

	public function escapeString($strVal, $throw = true)
	{
		$this->getConnection($throw);
		$ret = $this->_dbObj->escapeString($strVal);
		if(false === $ret && true === $throw){
			VseLog::warning("escape string failed [ str: $strVal ].");
			return $ret;
		}
		return $ret;
	}
}
?>
