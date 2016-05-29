<?php
class Route{
	private $controller;
	private $method;
	private $params = array();
	
	public function __construct(){
		$this->parseUrl();
	}
	
	/* url路由
	 * /index.php/index/welcome/goods/52
	 */
	protected function parseUrl(){
		global $config;
		$path_info = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'],'/') : "";
		//防止跨目录包含
		$path_info = str_replace("..", "", $path_info);
		if(!empty($_SERVER['QUERY_STRING'])){
			$query_string = $_SERVER['QUERY_STRING'];
			$query_string = str_replace('&', '/', $query_string);
			$query_string = str_replace('=', '/', $query_string);
			$path_info .= "/".$query_string;
		}
		$arr = explode('/', $path_info);
		$this->controller = array_shift($arr);
		$this->method = array_shift($arr);
		
		for($i=0;$i<count($arr);$i=$i+2){
			$this->params[$arr[$i]] = $arr[$i+1];
		}
		//默认路由
		$this->controller = !empty($this->controller) ? $this->controller : $config['DEFAULT_CONTROL'];
		$this->method = !empty($this->method) ? $this->method : $config['DEFAULT_ACTION'];
	}
	
	/* 调用controller文件
	 * 
	 */
	public function run(){
		global $config;
		$config['params'] = $this->params;
		$controlFile = ROOT_PATH . '/controller/' . $this->controller . '.php';
		if(!file_exists($controlFile)){
			exit('控制器不存在' . $controlFile);
		}
		include($controlFile);
		
		$className = ucwords($this->controller).'Controller';
		if(!class_exists($className)){
			exit('未定义的控制器类' . $className);
		}
		$instance = new $className();

		if(!method_exists($instance, $this->method)){
			exit('不存在的方法' . $this->method);
		}
		$methodName = $this->getMethod();
		$instance->$methodName();
	}
	
	private function getMethod(){
		return $this->method;
	}
}