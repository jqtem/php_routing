<?php
/**
* 一个高性能的php路由处理
* Code by zhanying<zhanying@venustech.com.cn> 
*/
class handler{
	protected static $_params = array(
		'var' => null,
		'route' => null,
		'status' => false,
		'action' => null,
		'value' => null,
	);
	protected static $_router = array();
	public function dispatch($router,$params){
		
		//未匹配成功
		if (!$params['status']) return null;

		$var = null;
		// 是否为动态路由
		if (!is_null(self::$_params['var'])) {
			//是动态路由
			$var = self::$_params['var'];
		}
		self::$_params = array_merge(self::$_params,array_change_key_case($params,CASE_LOWER));
		self::$_params['var'] = is_null($params['var'])?$var:$params['var'];
		self::$_router = $router;		
	}
}

class R{
	protected $LS = '{';
	protected $RS = '}';

	protected static $r = null;

	protected $_handler = null;

	public static function getRouter(){
		if (is_null(self::$r)) {
			self::$r = new R();
		}
		return self::$r;
	}

	public function add($router=null,$action='index'){
		//1.解析路由
		$router=trim($router,'/');
		//1.1路由初步解析
		$router = $this->parseRoute($router);

		//1.2添加路由动作
		$router['action'] = $action;
		//2.获取请求
		$request = $this->getRequest();
		// var_dump($request);
		//3.路由匹配
		$router = $this->matches($router,$request);		
		if (is_null($router)) {
			$router["route"]= 'Index';
			$router["action"]='index';
			$router["status"]= true;
			$router["var"]= null;
		}
		// var_dump($router);
		//4.处理结果
		$this->Router($router);
	}
	//路由跟踪
	public function bind(handler $handler = null){
		if ( is_null($this->_handler) && is_null($handler) ) {
			$handler = new handler();
		}
		$this->_handler = $handler;
	}

	public function getHandler(){
		if (is_null($this->_handler)) {
			$this->_handler = new handler();
		}
		return $this->_handler;
	}


	//路由匹配
	protected function matches($router,$request){
		$status= false;
		$router['value'] = null;
		if(empty($request)) return ;
		//静态路由全匹配
		if (is_null($router['var'])) {
			$status = ($request === $router['route']) ? true : false;
		}
		
		$regex = $router['regex'];

		if (isset($regex) && !is_null($router['var'])) {
			// $regex = '/'.$router['route'].'(?!\/)/';
			
			$flag = preg_match($regex, $request, $result,$result);
			if($flag){
				$status = (!$status);
				$router['value'] = $result[1];
			}	
		}
			
		$router['status'] = $status;

		return $router;
	}

	//路由处理
	protected function Router($router = null){
		$params = array();
		$params['var']		=	$router['var'];
		$params['route']	=	$router['route'];
		$params['status']	=	$router['status'];
		$params['action']	=	$router['action'];

		//静态路由判断
		$val = is_null($router['var']) ? null : $router['value'];
		$params['value'] = $val;

		$this->_handler->dispatch($router,$params);
	}

	/**
	 * 获取uri
	 */
	protected function getRequest(){
		$filter_param = array('<','>','"',"'",'%3C','%3E','%22','%27','%3c','%3e');
		$uri=str_replace($filter_param, '', $_SERVER['REQUEST_URI']);
		$pos=strpos($uri, '?');
    	if ($pos) $uri = substr($uri,0,$pos);
    	$request = str_replace($_SERVER['SCRIPT_NAME'], '', $uri);
		return trim($request,'/');
	}

	/**
	 * 路由解析
	 */
	protected function parseRoute($Route){
		$Lpos = strpos($Route , $this->LS);
		$Rpos = strpos($Route , $this->RS);
		$var = null;
		$regex = null;
		while( ($Lpos || $Rpos) !==false && $Rpos > $Lpos ){
			$rule = substr($Route,$Lpos,$Rpos - $Lpos +1);
			list($var,$regex) = explode(':' ,trim($rule,'{}') ,2);
			$regex = sprintf('(%s)', $regex);
			$Route = trim(str_replace($rule, '', $Route),'/');
			$nRoute = str_replace('/','\/',$Route);
			$regex = '/'.$nRoute.'\/'.$regex.'/';
			$Lpos = strpos($Route , $this->LS);
			$Rpos = strpos($Route , $this->RS);
		}

		return array(
			'var' => $var,
			'route' => $Route,
			'regex' => $regex,
		);
	}

	public static function Http_status($code){
		static $_status = array(
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',
            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',
            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
		);
		if(isset($_status[$code])) {
		    header('HTTP/1.1 '.$code.' '.$_status[$code]);
		    // 确保FastCGI模式下正常
		    header('Status:'.$code.' '.$_status[$code]);
		}
	}

	public static function Error($code=400,$msg='Error!'){
		self::Http_status(404);
		echo '<h1>'.$msg.'</h1>';
		die();
	}


}

?>