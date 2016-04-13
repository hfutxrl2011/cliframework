<?php

class BaeLog
{
    private static $instance = null;
    private $conf;

    private function formatMessage($logmsg)
    {
        $host = empty($_SERVER['HTTP_HOST']) ? $this->conf->customhost : $_SERVER["HTTP_HOST"];
        $source = empty($_SERVER['HTTP_CLIENTIP']) ? (empty($_SERVER["REMOTE_ADDR"]) ?
                $this->conf->customclient : $_SERVER["REMOTE_ADDR"]) : $_SERVER["HTTP_CLIENTIP"];
        $ip = empty($_SERVER['SERVER_ADDR'])?$this->conf->customip:$_SERVER['SERVER_ADDR'];
        return "$host $source $ip $logmsg";
    }
    
    private function selectServer()
    {
        if (!is_array($this->conf->logsvrs))
            return FALSE;

        $sum = 0;
        for ($i=0; $i<count($this->conf->logsvrs); $i++)
        {
            $sum += $this->conf->logsvrs[$i]['weight'];
        }

        $idx = rand() % $sum;

        $sum = 0;
        $sel = -1;
        for ($i=0; $i<count($this->conf->logsvrs); $i++)
        {
            $sum += $this->conf->logsvrs[$i]['weight'];
            if ($sum > $idx)
            {
                $sel = $i;
                break;
            }
        }

        if ($sel === -1)
            return FALSE;

        $server = array(
                'ip' => $this->conf->logsvrs[$sel]['ip'],
                'port' => $this->conf->logsvrs[$sel]['port'],
                );
        return $server;
    }

    private function openServer()
    {
        $server = $this->selectServer();
        if (!$server)
            return FALSE;

        if ($this->conf->splitmode)
            $path = empty($_SERVER['HTTP_HOST']) ? $this->conf->customhost : $_SERVER["HTTP_HOST"];
        else
            $path = $this->conf->path;
            

        $opt = array(
                'ip' => $server['ip'],
                'port' => $server['port'],
                'path' => $path,
                'filename' => $this->conf->filename,
                'auth' => $this->conf->auth,
                'connectlife' => $this->conf->connectlife,
                'level' => $this->conf->level,
                'compress' => $this->conf->compress,
                );
        return netcomlog_open($opt);
    }

    private function setConf($conf)
    {
        if ($conf instanceof PtagseBaeLogConfigure) // �޸�����
        {
            $this->conf = $conf;
        }
        else
        {
            $this->conf = new PtagseBaeLogConfigure(); // �޸�����
        }
    }

    private function logWrite2LocalFile($level, $logmsg)
    {
        $logtype = "NOTICE:";
        $localfilepath = $this->conf->localfilepath;
        switch ($level)
        {
            case 1:
                $logtype = "FATAL:";
                $localfilepath .= ".wf";
                break;

            case 2:
                $logtype = "WARNING:";
                $localfilepath .= ".wf";
                break;

            case 4:
                $logtype = "NOTICE:";
                break;

            case 8:
                $logtype = "TRACE:";
                break;
        
            case 16:
                $logtype = "DEBUG:";
                break;
        }

        $msg = $logtype . " " . date('Y-m-d H:i:s:') . " log * 0 " . $this->formatMessage($logmsg) . "\n"; 

        /* //can not access /tmp in safe mode, fix this later
        $fp = fopen($localfilepath, 'a');    
        if ($fp)
        {
            fwrite($fp, $msg);
            fclose($fp);
        }
        */
    }
    
    private function __construct($conf = null)
    {
        $this->setConf($conf);
    }

    /**
     * @brief ��ȡ��־����ʵ��
     *
     * @param $conf BaeLogConfigure����
     * @return  BaeLog or null
     * @retval  �ɹ��Ļ���BaeLog����ʧ�ܷ���null 
     * @see 
     * @note 
     * @author duxi
     * @date 2011/04/01 14:30:40
    **/
    public static function getInstance($conf = null)
    {
        $openflag = false;

        if (self::$instance === null)
        {
            self::$instance = new BaeLog($conf);
            $openflag = true;
        }
        else
        {
            if (($conf !== null && self::$instance->conf !== $conf)
                    || self::$instance->conf->splitmode)
                $openflag = true;
        }

        if (self::$instance !== null && $openflag)
        {
            $retrytimes = 0;
            while ($retrytimes < self::$instance->conf->retrytimes && !self::$instance->openServer()) 
            {
                $retrytimes++;
            }
        }
    
        return self::$instance;
    }


    /**
     * @brief ��ӡһ����־
     *
     * @param $level int ��־����
     * @param $logmsg string ��־����
     * @return  ��ӡ��־�ĳ��Ȼ���FALSE
     * @retval  
     * @see 
     * @note 
     * @author duxi
     * @date 2011/04/01 14:30:40
    **/
    public function logWrite($level, $logmsg)
    {
        $ret = netcomlog_write($level, "%s", $this->formatMessage($logmsg));
        if (!$ret)
        {
            $this->logWrite2LocalFile($level, $logmsg);
        }
        return $ret;
    }

    /**
     * @brief ��ӡһ�����������־
     *
     * @param $logmsg string ��־����
     * @return  ��ӡ��־�ĳ��Ȼ���FALSE
     * @retval 
     * @see 
     * @note 
     * @author duxi
     * @date 2011/04/01 14:30:40
    **/
    public function logFatal($logmsg)
    {
        $ret = NETCOMLOG_FATAL("%s", $this->formatMessage($logmsg));
        if (!$ret)
        {
            $this->logWrite2LocalFile(1, $logmsg);
        }
        return $ret;
    }

    /**
     * @brief ��ӡһ��������־
     *
     * @param $logmsg string ��־����
     * @return  ��ӡ��־�ĳ��Ȼ���FALSE
     * @retval 
     * @see 
     * @note 
     * @author duxi
     * @date 2011/04/01 14:30:40
    **/
    public function logWarning($logmsg)
    {
        $ret = NETCOMLOG_WARNING("%s", $this->formatMessage($logmsg));
        if (!$ret)
        {
            $this->logWrite2LocalFile(2, $logmsg);
        }
        return $ret;
    }

    /**
     * @brief ��ӡһ��Notice��־
     *
     * @param $logmsg string ��־����
     * @return  ��ӡ��־�ĳ��Ȼ���FALSE
     * @retval  
     * @see 
     * @note 
     * @author duxi
     * @date 2011/04/01 14:30:40
    **/
    public function logNotice($logmsg)
    {
        $ret = NETCOMLOG_NOTICE("%s", $this->formatMessage($logmsg));
        if (!$ret)
        {
            $this->logWrite2LocalFile(4, $logmsg);
        }
        return $ret;
    }

    /**
     * @brief ��ӡһ��Trace��־?     *
     * @param $logmsg string ��־����
     * @return  ��ӡ��־�ĳ��Ȼ���FALSE
     * @retval  
     * @see 
     * @note 
     * @author duxi
     * @date 2011/04/01 14:30:40
    **/
    public function logTrace($logmsg)
    {
        $ret = NETCOMLOG_TRACE("%s", $this->formatMessage($logmsg));
        if (!$ret)
        {
            $this->logWrite2LocalFile(8, $logmsg);
        }
        return $ret;
    }

    /**
     * @brief ��ӡһ��Debug��־
     *
     * @param $logmsg string ��־����
     * @return  ��ӡ��־�ĳ��Ȼ���FALSE
     * @retval  
     * @see 
     * @note 
     * @author duxi
     * @date 2011/04/01 14:30:40
    **/
    public function logDebug($logmsg)
    {
        $ret = NETCOMLOG_DEBUG("%s", $this->formatMessage($logmsg));
        if (!$ret)
        {
            $this->logWrite2LocalFile(16, $logmsg);
        }
        return $ret;
    }

    /**
     * @brief ��ȡphp-netcomlog�Ĵ�����
     *
     * @return int php-netcomlog������ 
     * @retval  
     * @see 
     * @note 
     * @author duxi
     * @date 2011/04/01 14:30:40
    **/
    public function getErrNo()
    {
        return netcomlog_errno();
    }

    /**
     * @brief ��ȡphp-netcomlog�Ĵ�����Ϣ?     *
     * @return int php-netcomlog������Ϣ 
     * @retval  
     * @see 
     * @note 
     * @author duxi
     * @date 2011/04/01 14:30:40
    **/
    public function getErrMsg()
    {
        return netcomlog_error_message();
    }
}




/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>

