<?php
class Controller
{
	protected $view = NULL;
	
	public function __construct(){
		$this->view = new View();
	}
	
	public function redirect($url){
		header("Location: ".APP_PATH."/".$url);
		exit();
	}
	
	public function alert($msg){
		echo "<script>alert('".$msg."');window.history.go(-1);</script>";
	}
	
	/*
	 * 登录状态判断
	 * login=0 未登录时候的判断
	 * login=1 登录时候的判断,login页面不用显示
	 */
	public function auth($login=0){
		if($login==0){
			if(!isset($_SESSION['login']) || $_SESSION['login'] != 1){
				$this->redirect('index.php/admin');
			}
		}
		if($login==1){
			if(isset($_SESSION['login']) && $_SESSION['login'] == 1){
				$this->redirect('index.php/admin/admin_index');
			}
		}
	}
	
    public function assign($var, $value = ''){
    	$this->view->assign($var, $value);
	}
    
    public function display($file)
    {
        $this->view->display($file);
    }

    protected function Model($modelName){
		$modelFile = ROOT_PATH . '/model/' . $modelName . '.php';
		!file_exists($modelFile) && exit('模型' . $modelName . '不存在');
		include($modelFile);
		$class = ucwords($modelName);
		!class_exists($class) && exit('模型' . $modelName . '未定义');
		$model = new $class();
		return $model;
	}
}