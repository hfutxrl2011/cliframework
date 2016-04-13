<?php
/**
 * http request锛屽惈鏈塰ttp璇锋眰鐨勭浉鍏虫暟鎹紝姣斿GET POST COOKIE URL_PARSE
 * 鍏朵腑鎵�湁鐨勬暟鎹兘杩涜浜嗙浉搴旂殑缂栫爜杞崲宸ヤ綔銆�
 * @author xuliqiang@baidu.com
 * @since 2009-12-1
 * @example 
 * 
 * @tutorial 
 *
 */
class Bingo_Http_Request
{
    const METHOD_POST = 'POST';
    
    const METHOD_GET = 'GET';
    /**
     * 璁剧疆缂栫爜鏂瑰紡鐨勫瓧娈靛悕绉�
     *
     */
    protected static $_strAutoDetectEncodeName = 'ie';
    /**
     * http璇锋眰杩囨潵鐨勭紪鐮佹柟寮�
     *
     * @var string:utf-8/gbk
     */
    protected static $_strHttpEncode = 'UTF-8'/*UTF8DIFF*/;
    /**
     * 鍐呴儴浠ｇ爜缂栫爜鏂瑰紡
     *
     * @var string:utf-8/gbk
     */
    protected static $_strInternalEncode = 'UTF-8'/*UTF8DIFF*/;
    /**
     * 杩涜缂栫爜杞崲鐨勫簱锛屽彲浠ラ�鎷﹗conv鎴栬�mb_string
     * 鍏蜂綋鍙傝�Bingo_Encode搴撶被
     *
     * @var string
     */
    protected static $_strEncodeEngine = 'uconv';
    /**
     * $_GET鏁扮粍鐨勬暟鎹紝瀵瑰瓧绗︾紪鐮佽繘琛屼簡澶勭悊
     *
     * @var array
     */
    protected static $_arrGet = array();
    /**
     * $_POST鏁扮粍鐨勬暟鎹紝瀵瑰瓧绗︾紪鐮侀兘杩涜浜嗗鐞�
     *
     * @var array
     */
    protected static $_arrPost = array();
    /**
     * $_COOKIE鏁扮粍鐨勬暟鎹紝瀵瑰瓧绗︾紪鐮佽繘琛屼簡澶勭悊
     *
     * @var array
     */
    protected static $_arrCookie = array();
    /**
     * $_SERVER鏁扮粍鐨勬暟鎹紝瀵瑰瓧绗︾紪鐮佽繘琛屼簡澶勭悊
     *
     * @var array
     */
    protected static $_arrServer = array();
    /**
     * http router
     *
     * @var string
     */
    protected static $_strHttpRouter = '';
    protected static $_arrHttpRouter = array();
    /**
     * 鐢ㄤ簬dispatch鐨剅outer
     * @var unknown_type
     */
    protected static $_strDispatchRouter = '';
    protected static $_arrDispatchRouter = array();
    /**
     * router涓殑鍙傛暟鍙橀噺
     *
     * @var array
     */
    protected static $_arrRouterParams = array();
    protected static $_arrDict = array();
    /**
     * URL涓弬鏁版暟缁�
     *
     * @var array
     */
    protected static $_arrParams = array();
    protected static $_arrParamsFormat = null;
    protected static $_strMethod = '';
    private static $_boolHttpEncodeHasDetect = false;//鏄惁杩涜HTTP缂栫爜绫诲瀷鐨勬鏌�
    private static $_boolIsInit = false;//鏄惁宸茬粡鍒濆鍖�
   
    private static $_boolIsHttps = false;

    private static $_basicString = '';	



    /**
     * 浠嶥ict涓幏鍙栨暟鎹�
     * @param unknown_type $strKey
     * @param unknown_type $mixDefaultValue
     */
    public static function getDict($strKey, $mixDefaultValue = null)
    {
        if (empty($strKey)) {
            return self::$_arrDict;
        }
        if (array_key_exists($strKey, self::$_arrDict)) {
            return self::$_arrDict[$strKey];
        }
        return $mixDefaultValue;
    }
    /**
     * 璁剧疆鏁版嵁鍒癉ict涓幓
     * @param unknown_type $strKey
     * @param unknown_type $mixValue
     */
    public static function setDict($strKey, $mixValue)
    {
        self::$_arrDict[$strKey] = $mixValue;
    }
    /**
     * 璁剧疆HTTP鐨勭紪鐮佹牸寮忥紝璋冪敤蹇呴』鍏堜簬init鍑芥暟鐨勮皟鐢�
     *
     * @param string $_strHttpEncode
     */
    public static function setHttpEncode($_strHttpEncode) 
    {
        self::$_strHttpEncode = $_strHttpEncode;
        self::$_boolHttpEncodeHasDetect = TRUE;
    }
    /**
     * 璁剧疆鍐呴儴缂栫爜鏍煎紡锛岃皟鐢ㄥ繀椤诲厛浜巌nit鍑芥暟鐨勮皟鐢�
     *
     * @param string $_strInternalEncode
     */
    public static function setInternalEncode($_strInternalEncode)
    {
        self::$_strInternalEncode = $_strInternalEncode;
    }
    /**
     * 鍒濆鍖栵紝浣跨敤httpRequest锛屽繀椤婚鍏堣皟鐢ㄨ繖涓嚱鏁般�
     * 瀹屾垚缂栫爜妫�祴鍙婄紪鐮佽浆鍖栧伐浣溿�
     * @param array $arrConfig
     * {
     * 		httpEncode : string
     * 		internalEncode : string
     * 		autoDetectEncodeName : string
     * 		encodeEngine : string (uconv 鎴栬�mb_string)
     * 		
     * }
     *
     */
    public static function init($arrConfig = array())
    {
    	if (self :: $_boolIsInit) 
    		return true;
    	if (isset( $arrConfig ['httpEncode'] )) {
    		self :: setHttpEncode( $arrConfig ['httpEncode'] );
    	} else {
    		if (isset($arrConfig['autoDetectEncodeName'])) {
    			self :: $_strAutoDetectEncodeName = $arrConfig['autoDetectEncodeName'];
    		}
    		self :: _detectHttpEncode();
    	}
        if (isset( $arrConfig ['internalEncode'] )) {
        	self :: setInternalEncode( $arrConfig ['internalEncode'] );    	
        }
        if (isset($arrConfig['encodeEngine'])) {
        	self::$_strEncodeEngine = $arrConfig['encodeEngine'];
        }

	 if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on')
		self::$_boolIsHttps = true;
	else 
		self::$_boolIsHttps = false;

        //stripslashes
        if (get_magic_quotes_gpc()) {
            $_POST      = array_map( array('Bingo_Http_Request', '_stripslashesDeep'), $_POST );
            $_GET       = array_map( array('Bingo_Http_Request', '_stripslashesDeep'), $_GET );
            $_COOKIE    = array_map( array('Bingo_Http_Request', '_stripslashesDeep'), $_COOKIE );
            $_REQUEST   = array_map( array('Bingo_Http_Request', '_stripslashesDeep'), $_REQUEST );   
            $_SERVER    = array_map( array('Bingo_Http_Request', '_stripslashesDeep'), $_SERVER );
        }
        //缂栫爜杞崲    
        if (self::$_strHttpEncode != self::$_strInternalEncode) {
            //闇�杩涜缂栫爜杞崲        
            require_once 'Bingo/Encode.php';
            self::$_arrGet = Bingo_Encode::convertGet($_GET, self::$_strInternalEncode, self::$_strHttpEncode, self::$_strEncodeEngine);
            self::$_arrPost = Bingo_Encode::convertPost($_POST, self::$_strInternalEncode, self::$_strHttpEncode, self::$_strEncodeEngine);
            self::$_arrCookie = Bingo_Encode::convertGet($_COOKIE, self::$_strInternalEncode, self::$_strHttpEncode, self::$_strEncodeEngine);
        } else {
            self::$_arrGet = $_GET;
            self::$_arrPost = $_POST;
            self::$_arrCookie = $_COOKIE;
        }
        self :: $_arrServer = $_SERVER;
        self :: $_boolIsInit = true;
        return true;
    }
    /**
     * 鑾峰彇Request Method
     */
    public static function getMethod()
    {
        if (empty (self::$_strMethod)) {
            self::$_strMethod = strtoupper(strip_tags(trim(self::getServer('REQUEST_METHOD', self::METHOD_GET))));
        }
        return self::$_strMethod;
    }
    /**
     * 鏄惁鏄疨OST鏂规硶
     */
    public static function isPost()
    {
        return (bool)( self::METHOD_POST == self::getMethod());
    }
    /**
     * 鏄惁鏄疓ET鏂规硶
     */
    public static function isGet()
    {
        return (bool)( self::METHOD_GET == self::getMethod());
    }
    
    public static function setHttpRouter($strHttpRouter, $arrHttpRouter = array())
    {
    	self :: $_strHttpRouter = self :: $_strDispatchRouter = $strHttpRouter;
    	if (! empty($arrHttpRouter))self :: $_arrHttpRouter = self :: $_arrDispatchRouter = $arrHttpRouter;
    }
    public static function setDispatchRouter($strDispatchRouter)
    {
    	self :: $_strDispatchRouter = $strDispatchRouter;
    }
    public static function setArrDispathRouter($arrDispatchRouter)
    {
    	self :: $_arrDispatchRouter = $arrDispatchRouter;
    }
    public static function getDispatchRouter()
    {
    	return self :: $_strDispatchRouter;
    }
    public static function getArrDispatchRouter()
    {
    	return self :: $_arrDispatchRouter;
    }
    /**
     * 浠嶩TTP璇锋眰涓幏鍙栧師濮嬬殑router
     *
     * @return string
     */
    public static function getStrHttpRouter()
    {
    	return self::$_strHttpRouter;
    }
    
    public static function getArrHttpRouter()
    {
    	return self::$_arrHttpRouter;
    }
    /**
     * 璁剧疆HTTP璺敱涓殑鍙傛暟锛屽鏋滀娇鐢ㄤ簡瑙勫垯璺敱鐨勬椂鍊欙紝浼氭湁鐢ㄣ�
     * 姣斿璇�
     * url : /club/1234/thread/23456.html
     * regex : /club/:club_id/thread/:thread_id.html
     * 灏卞彲浠ラ�杩嘊ingo_Http_Request :: getRouterParam('club_id')杩斿洖1234
     * @param arr $arrRouterParams
     */
    public static function setRouterParams($arrRouterParams)
    {
    	self::$_arrRouterParams = $arrRouterParams;
    }
    
    public static function addRouterParams($key, $value)
    {
    	self :: $_arrRouterParams[$key] = $value;
    }
    public static function emptyRouterParams()
    {
    	self :: $_arrRouterParams = array();
    }
    public static function getRouterParam( $key, $defaultValue = null )
    {
        return self::_getFromArray($key, $defaultValue, self::$_arrRouterParams);
    }
    
    public static function setParams($arrParams)
    {
    	self :: $_arrParams = $arrParams;
    }
    /**
     * 鑾峰彇URL涓$intIndex鐨勫弬鏁帮紝姣斿/news-1234-pn-2.html
     * 鑾峰彇绗�涓弬鏁板氨鏄�234,绗簩涓弬鏁版槸pn
     *
     * @param int $intIndex
     * @param mixed $defaultValue
     * @return mixed
     */
    public static function getParamByIndex($intIndex, $defaultValue = null)
    {
    	-- $intIndex;
    	if (isset( self :: $_arrParams[ $intIndex ] )) {
    		return  self :: $_arrParams[ $intIndex ];
    	}
    	return $defaultValue;
    }
    /**
     * key value鑾峰彇鑾峰彇URL涓殑鍙傛暟
     * @param $strKey
     * @param $defaultValue
     */
    public static function getParam($strKey, $defaultValue = null)
    {
    	if (is_null(self :: $_arrParamsFormat)) {
    		self :: $_arrParamsFormat = array();
    		if (! empty(self :: $_arrParams)) {
    			$_intNum = count(self::$_arrParams);
    			$_intNum --;
    			if ($_intNum > 0) {
    				for($i=0; $i < $_intNum; $i++) {
    					self :: $_arrParamsFormat[self::$_arrParams[$i]] = self::$_arrParams[$i+1];
    				}
    			}
    		} 
    	}
    	if (isset(self::$_arrParamsFormat[$strKey])) {
    		return self::$_arrParamsFormat[$strKey];
    	}
    	return $defaultValue;
    }
    /**
     * 鑾峰彇鎵�湁鐨勫弬鏁�
     *
     * @return array
     */
    public static function getParams()
    {
    	return self :: $_arrParams;
    }    
    /**
     * 鑾峰彇HTTP璇锋眰涓殑涓�釜鏁版嵁
     *
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public static function get($key, $defaultValue=null)
    {
        if ( isset( self::$_arrGet[$key] ) ) {
            return self::$_arrGet[$key];
        } elseif ( isset( self::$_arrPost[$key] )) {
            return self::$_arrPost[$key];
        } else {
            return $defaultValue;
        }
    }
    /**
     * 鑾峰彇GET鏁版嵁涓殑涓�釜鍏冪礌锛屽鏋滀笉瀛樺湪鍒欒繑鍥為粯璁ゅ�銆�
     * 濡傛灉$key=null锛屽垯杩斿洖鏁翠釜GET鏁扮粍
     *
     * @param string $key
     * @param string $defaultValue
     * @return mixed
     */
    public static function getGet($key=null, $defaultValue=null) 
    {
        self::init();
        return self::_getFromArray($key, $defaultValue, self::$_arrGet);
    }
    /**
     * 鑾峰彇POST鏁版嵁涓殑涓�釜鍏冪礌锛屽鏋滀笉瀛樺湪鍒欒繑鍥為粯璁ゅ�銆�
     * 濡傛灉$key=null锛屽垯杩斿洖鏁翠釜POST鏁扮粍
     *
     * @param string $key
     * @param string $defaultValue
     * @return mixed
     */
    public static function getPost($key=null, $defaultValue=null) 
    {
        self::init();
        return self::_getFromArray($key, $defaultValue, self::$_arrPost);
    }
    /**
     * 鑾峰彇COOKIE鏁版嵁涓殑涓�釜鍏冪礌锛屽鏋滀笉瀛樺湪鍒欒繑鍥為粯璁ゅ�銆�
     * 濡傛灉$key=null锛屽垯杩斿洖鏁翠釜COOKIE鏁扮粍
     *
     * @param string $key
     * @param string $defaultValue
     * @return mixed
     */
    public static function getCookie($key=null, $defaultValue=null) 
    {
        self::init();
        return self::_getFromArray($key, $defaultValue, self::$_arrCookie);
    }
    
    public static function getServer($key=null, $defaultValue=null)
    {
        self::init();
    	return self::_getFromArray($key, $defaultValue, self::$_arrServer);
    }
    
    private static function _getFromArray($key, $defaultValue, $array)
    {
        if (is_null($key)) {
            return $array;
        } elseif (isset($array[$key])) {
            return $array[$key];
        } else {
            return $defaultValue;
        }
    }
    /**
     * 瀵规暟缁勯噷闈㈡瘡涓�釜鍏冪礌閮借皟鐢╯tripslashes
     *
     * @param mixed $value
     * @return mixed
     */
    private static function _stripslashesDeep($value)
    {
        $value = is_array($value) ? array_map(array('Bingo_Http_Request', '_stripslashesDeep'), $value) : stripslashes($value);            
        return $value;
    }
    /**
     * 妫�祴HTTP鐨勭紪鐮佹柟寮�
     *
     */
    private static function _detectHttpEncode()
    {
        if (self::$_boolHttpEncodeHasDetect) return true;
        $strEncode = '';
        if (isset($_GET[self::$_strAutoDetectEncodeName])) {
            $strEncode = $_GET[self::$_strAutoDetectEncodeName];            
        } elseif (isset($_POST[self::$_strAutoDetectEncodeName])) {
            $strEncode = $_POST[self::$_strAutoDetectEncodeName];
        }
        if (! empty($strEncode)) {
            self::$_strHttpEncode = strtolower(trim(strip_tags($strEncode)));
        }
    }
    
    public static function arrayFilterEmpty($arrInput)
    {
    	$arrOutput = array();
		if (! empty($arrInput)) {
			foreach ($arrInput as $_strNode) {
				if (trim($_strNode) === '') {
					
				} else {
					$arrOutput[] = $_strNode;
				}
			}
		}
		return $arrOutput;
    }



    public static function isHttps()
    {
	      	return self::$_boolIsHttps;
    }

    public static function getBasicString()
    {
	if (empty(self::$_basicString)) {
     		self::$_basicString =self::getMethod();
		$url = strtolower($_SERVER['REQUEST_URI']);
		$pos = strpos($url, "?");
		if(false !== $pos) {
		   $url = substr($url, 0, $pos);
		}
		self::$_basicString .= "http://" . trim(strtolower ($_SERVER ['HTTP_HOST']), '/') . "/" . trim( $url , '/');
		$arr = array_merge (   self::$_arrGet ,   self::$_arrPost );		
		ksort($arr);		
		foreach ($arr as $key => $value) {
		    if($key != "sign") {
		        self::$_basicString  .= $key . "=" . $value;
		    }
		}
		
	}
	return self::$_basicString;
    }	

   

}


