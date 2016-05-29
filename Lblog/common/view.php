<?php

class View{
	protected $vars = array();
	
	/* $var模板变量名称, $value对应的值
	 * 
	 */
    public function assign($var, $value){
   		if(is_array($var)){
        	$this->vars = array_merge($this->vars, $var);
        }
        else{
        	$this->vars[$var] = $value;
        }
     }
	
	public function display($file){
		include(ROOT_PATH . '/common/template.php');
		$this->template = Template::GetInstance();
		$this->template->templatePath = ROOT_PATH . '/view/';
		$this->template->cache = TRUE;
		$this->template->cachePath = ROOT_PATH . '/cache/';
		$this->template->cacheLifeTime = 1;
		$this->template->templateSuffix = '.html';
		$this->template->leftTag = '{';
		$this->template->rightTag = '}';
		$templateFile = ROOT_PATH . '/view/' . $file . '.html';
		if(!file_exists($templateFile)){
			exit('模板文件' . $file . '不存在');
		}
		$this->template->display($file, $this->vars);
	}
}