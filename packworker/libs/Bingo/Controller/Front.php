<?php
/**
 * 璺敱杞彂鎺у埗鍣紝瀹屾垚璺敱杞彂鐨勫伐浣溿�
 * 
 * @author xuliqiang <xuliqiang@baidu.com>
 * @package bingo2.0
 * @since 2010-02-24
 */
require_once 'Bingo/Http/Request.php';
require_once 'Bingo/Http/Router/Abstract.php';
class Bingo_Controller_Front
{
	private static $_singleton     = null;
	/**
	 * 闈欐�璺敱 瀵硅薄
	 * @var Bingo_Router_Static
	 */
	protected $_objStaticRouter    = null;
	/**
	 * 瑙勫垯璺敱 瀵硅薄
	 * @var Bingo_Router_Rule
	 */
	protected $_objRuleRouter      = null;
	/**
	 * 杩涜鍒嗗彂鐨勫璞℃暟缁勶紝閲岄潰姣忎竴涓兘鏄竴涓猟ispatcher
	 * @var array
	 */
	protected $_arrDispatchHash    = array();
	/**
	 * 閰嶇疆淇℃伅
	 * @var array
	 */
	protected $_arrConfig          = array();
	/**
     * 鐢ㄤ簬鍒嗗彂鐨剅outer
     *
     * @var string
     */
    protected $_strDispatchRouter  = '';
    /**
     * 濡傛灉dispatchRouter涓虹┖锛屽垯閲囩敤璇ラ粯璁ょ殑Router銆�
     * @var string
     */
    protected $_strDefaultRouter   = 'index';
    /**
     * 濡傛灉鎵�湁鐨刣ispatcher閮絛ispatch澶辫触鍚庯紝灏嗛噰鐢ㄨrouter閲嶆柊杩涜鍒嗗彂銆�
     * @var string
     */
    protected $_strNotFoundRouter  = 'index';
    /**
     * filterActions锛屼竴涓摼琛紝鍦ㄦ瘡涓猵rocessAction澶勭悊涔嬪墠椤哄簭鎵ц
     * @var unknown_type
     */
    protected $_arrFilterActions   = array();
    /**
     * 
     * @var unknown_type
     */
    protected $_arrEndActions      = array();
    /**
     * Action鏍堬紝娉ㄦ剰鏄�搴忕殑
     * @var unknown_type
     */
    protected $_objActions         = array();

    public function addFilterAction($objAction)
    {
        $this->_arrFilterActions[] = $objAction;
    }
    
    public function setFilterActions($arrActions)
    {
        $this->_arrFilterActions   = $arrActions;
    }
    
    public function addEndAction($objAction)
    {
        $this->_arrEndActions[]    = $objAction;
    }
    
    public function setEndActions($arrActions)
    {
        $this->_arrEndActions      = $arrActions;
    }
    
	private function __construct($arrConfig=array())
	{
		$this->_arrConfig = $arrConfig;
		Bingo_Http_Request::init($arrConfig);
		
		if (isset($arrConfig['httpRouter']) && is_subclass_of($arrConfig['httpRouter'], 'Bingo_Http_Router_Abstract') ) {
			$objHttpRouter = $arrConfig['httpRouter'];
		} else {
			require_once 'Bingo/Http/Router/Pathinfo.php';
			$objHttpRouter = new Bingo_Http_Router_Pathinfo($arrConfig);
		}
		if (isset($arrConfig['defaultRouter'])) {
			$this->_strDefaultRouter = $arrConfig['defaultRouter'];
		}
		if (isset($arrConfig['notFoundRouter'])) {
			$this->_strNotFoundRouter = $arrConfig['notFoundRouter'];
		}
		$objHttpRouter->getHttpRouter();
	}	
	/**
	 * 鍗曚欢妯″紡锛岃幏鍙栧埌涓�釜瀹炰緥瀵硅薄
	 * @param array $arrConfig
	 * {
	 * 		httpEncode : string 杈撳叆鍙傛暟鐨勭紪鐮佺被鍨�
     * 		internalEncode : string 鍐呴儴閲囩敤鐨勭紪鐮佺被鍨嬶紝閫氬父鏄痝bk
     * 		autoDetectEncodeName : string 鑷姩鑾峰彇缂栫爜绫诲瀷鐨刱ey銆傚鏋滆缃簡httpEncode锛屽垯涓嶉渶瑕佽缃鍙橀噺     * 
     * 		encodeEngine : string (uconv 鎴栬�mb_string)
     * 		httpRouter : 鑾峰彇router鐨勫璞★紝闇�鏄疊ingo_Http_Router_Abstract鐨勫瓙绫汇�榛樿閲囩敤Bingo_Http_Router_Pathinfo
     * 		濡傛灉閲囩敤Bingo_Http_Router_Pathinfo锛岄偅涔堣繕鏈夊嚑涓弬鏁�
     * 		{
     * 		sepOfRouterAndParams 锛�
     * 		sepOfParams 锛�
     * 		endOfParams 锛�
     * 		beginRouterIndex 锛�
     * 		sepOfRouter 锛�
     * 		defaultHttpRouter 锛�
     * 		usePathinfo 锛�
     * 		}
     * 		defaultRouter : 濡傛灉dispatchRouter涓虹┖锛屽垯閲囩敤璇ラ粯璁ょ殑Router銆�
     * 		notFoundRouter : 濡傛灉鎵�湁鐨刣ispatcher閮絛ispatch澶辫触鍚庯紝灏嗛噰鐢ㄨrouter閲嶆柊杩涜鍒嗗彂銆�
     * 		濡傛灉閲囩敤榛樿鐨勮嚜鍔ㄥ垎鍙戯紝鍒欒繕鏈変互涓嬬殑鍙傛暟
     * 		actionDir 锛�actions鐨勭洰褰�
     * 		actionFileSuffix 锛�鏂囦欢鍚庣紑锛岄粯璁ゆ槸.php
     * 		actionClassNameSuffix : 绫诲悕鐨勫悗缂�紝榛樿鏄疉ction
     * }	
	 */
	public static function getInstance($arrConfig=array())
    {
	
        if (is_null(self::$_singleton)) {
            self::$_singleton = new Bingo_Controller_Front($arrConfig);
        }
        return self::$_singleton;
    }
    /**
     * 娉ㄥ唽鍒嗗彂鍣�
     * @param $objDispatch
     */
	public function registerDispatch($objDispatch)
	{
	    if (method_exists($objDispatch, 'dispatch')) {
	        $this->_arrDispatchHash[] = $objDispatch;  
	        return true;
	    }
	    trigger_error('registerDispatch error! invalid!', E_USER_WARNING);
	    return false;
	}    
	/**
	 * 娣诲姞闈欐�璺敱
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function addStaticRouter($key, $value = NULL)
	{
	    if (is_null($this->_objStaticRouter)) {
	    	require_once 'Bingo/Router/Static.php';
	        $this->_objStaticRouter = new Bingo_Router_Static();
	    }
	    if (is_null($value)) $value = $key;
	    $this->_objStaticRouter->add($key,$value);
	    return $this;
	}
	/**
	 * 娣诲姞璺敱瑙勫垯
	 * @example 
	 * $rule = array{
	 * 	   'rule' => array('test', ':key', 'here'),
 	 *     'regex' => array(
 	 *         ':key' => '[0-9]',
 	 *     ),
 	 * }
 	 * $this->addRouterRule('test', $rule);
	 *
	 * @param string $key
	 * @param array $rule
	 * {
	 * 		rule : array
	 * 		regex : array
	 * }
	 * @return $this
	 */
	public function addRouterRule($key, $rule)
	{
	    if (is_null($this->_objRuleRouter)) {
	    	require_once 'Bingo/Router/Rule.php';
	        $this->_objRuleRouter = new Bingo_Router_Rule();
	    }
	    $this->_objRuleRouter->add($key, $rule);
	    return $this;
	}	
	/**
	 * 鑾峰彇鐢ㄤ簬dipatch鐨勮矾鐢�
	 *
	 * @return string
	 */
	public function getDispatchRouter()
	{
	    if (empty($this->_strDispatchRouter)) {
	        return $this->_geneDispatchRouter();
	    }
	    return $this->_strDispatchRouter;
	}
	/**
	 * 鑾峰彇Action,鏈�悗鎵ц鐨勪竴涓�
	 */
	public function getAction()
	{
	    return $this->getLastAction();
	}	
	public function getActions()
	{
	    return $this->_objActions;
	}
	/**
	 * 鑾峰彇鏈�悗鎵ц鐨勪竴涓狝ction
	 */
	public function getLastAction()
	{
	    if (! empty($this->_objActions)) {	    
	        return $this->_objActions[0];
	    }
	    return false;
	}
	/**
	 * 鑾峰彇绗竴涓墽琛岀殑Action
	 */
	public function getFirstAction()
	{
	    if (! empty($this->_objActions)) {	    
	        return $this->_objActions[count($this->_objActions)-1];
	    }
	    return false;
	}
	
	public function dispatchByRouter($strDispatchRouter, $bolNoFrameworkDispatch = true)
	{
	    $boolDispatched = false;
	    if (empty($this->_arrDispatchHash)) {
	    	require_once 'Bingo/Dispatch/Auto.php';
	    	$this->_arrDispatchHash[] = new Bingo_Dispatch_Auto($this->_arrConfig);
	    }
	    foreach ($this->_arrDispatchHash as $_objDispatch) {
	    	$boolDispatched = $_objDispatch->dispatch($strDispatchRouter);
	    	if ($boolDispatched) {
	    	    if (method_exists($_objDispatch, 'getAction')) {
	    	        $this->_objActions[] = $_objDispatch->getAction();
	    	    }
	    	    break;
	    	}
	    }
	    if (! $boolDispatched) {
	    	trigger_error('dispatch error!router[' . $strDispatchRouter . '],goto notFoundRouter', E_USER_WARNING);
	    } else {
	    	return true;
	    }
	    //not Found dispatch
    	foreach ($this->_arrDispatchHash as $_objDispatch) {
	    	$boolDispatched = $_objDispatch->dispatch($this->_strNotFoundRouter);
	    	if ($boolDispatched) {
	    	    if (method_exists($_objDispatch, 'getAction')) {
	    	        $this->_objActions[] = $_objDispatch->getAction();
	    	    }
	    	    break;
	    	}
	    }
	    
    	if (! $boolDispatched) {
	    	throw new Exception('dispatch error!router[' . $strDispatchRouter . ']');
	    }
	    return false;
	}
    /**
     * 鍒嗗彂
     */
    public function dispatch()
    {
        $bolDispatch = true;
        if (! empty($this->_arrFilterActions)) {
            $mixRet = true;
            foreach ($this->_arrFilterActions as $_objAction) {
                $mixRet = $this->_runAction($_objAction);
                if ($mixRet == Bingo_Action_Filter::FILTER_END) {
                    trigger_error('filterAction:' . get_class($_objAction). ' ret=FILTER_END', E_USER_NOTICE);
                    //鐩存帴璺宠浆鍒癆ction
                    break;
                }elseif ($mixRet == Bingo_Action_Filter::FILTER_ACTION_END) {
                    //鐩存帴璺宠浆鍒癳nd Action
                    trigger_error('filterAction:' . get_class($_objAction). ' ret=FILTER_ACTION_END', E_USER_NOTICE);
                    $bolDispatch = false;
                    break;
                }elseif ($mixRet == Bingo_Action_Filter::FILTER_ALL_END) {
                    //鍏ㄩ儴缁撴潫
                    trigger_error('filterAction:' . get_class($_objAction). ' ret=FILTER_ALL_END', E_USER_NOTICE);
                    return ;
                }
            }
        }
        //filterActions end
        if ($bolDispatch) {
    		 $strDispatchRouter = $this->getDispatchRouter();
	        $this->dispatchByRouter($strDispatchRouter);
        }
        //endActions begin
        if (! empty($this->_arrEndActions)) {
            foreach ($this->_arrEndActions as $_objAction) {
                $mixRet = $this->_runAction($_objAction);
                if ($mixRet === false) {
                    trigger_error('endAction:' . get_class($_objAction). ' ret=FILTER_ALL_END', E_USER_NOTICE);
                    return ;
                }
            }
        }
    }
    
    protected function _runAction($objAction)
    {
        $mixRet = true;
        if (method_exists($objAction, 'init')) {
            $objAction->init();
        }
        if (method_exists($objAction, 'execute')) {
            $mixRet = $objAction->execute();
        }
        return $mixRet;
    }
    /**
     * 浜х敓dispatchRouter锛屾湁涓ょfilter锛�static 鍜�瑙勫垯
     */
	protected function _geneDispatchRouter()
	{	    
	    $strHttpRouter = Bingo_Http_Request::getStrHttpRouter();
	    if (! is_null($this->_objStaticRouter)) {
	        //static
	    	$this->_strDispatchRouter = $this->_objStaticRouter->getDispatchRouter($strHttpRouter);
	    	if ($this->_strDispatchRouter) return $this->_strDispatchRouter;
	    }
	    if (! is_null($this->_objRuleRouter)) {
	        //rule
	        $this->_strDispatchRouter = $this->_objRuleRouter->getDispatchRouter($strHttpRouter);
	        if ($this->_strDispatchRouter) return $this->_strDispatchRouter;
	    }	    
	    //default
	    $this->_strDispatchRouter = $strHttpRouter;
	    if (empty($this->_strDispatchRouter)) {
	    	$this->_strDispatchRouter = $this->_strDefaultRouter;
	    }
	    return $this->_strDispatchRouter;
	}
}