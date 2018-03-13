<?php

/**
 * 案例
 */

define('__M__', 'app');
define('__ROOT__', dirname(str_replace('\\', '/', __FILE__)));
define('__APP_PATH__', __ROOT__ . '/' . __M__);
define('__APP_CONTROLLER__', __APP_PATH__ . '/' . 'Controller');

require __ROOT__ .'/route/R.class.php';
/**
* 集中路由仲裁
*/
class MyHandler extends handler{
	public function __construct(){		
		spl_autoload_register(array(__CLASS__,'_autoload'));
	}
	public function run(){
		if (!self::$_params['status']) {
			R::Error(404,'Not Found.');
		}
		$c = ucwords(self::$_params['route']);
		$a = self::$_params['action'];
		$c .= 'Controller';
		$obj = new $c();
		$obj->$a(self::$_params);

	}
	private function _autoload($className){
		include __APP_CONTROLLER__ . '/' .$className.'.class.php';
	}
}
$handler = new MyHandler();

$Router=R::getRouter();
$Router->bind($handler);

$Router->add('user/','home');
$Router->add('admin','home');
$Router->add('user/{uid:u_\d+$}','index');

$handler->run();



?>