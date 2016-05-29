<?php
// +----------------------------------------------------------------------
// | Tomorrow  Framework  系统模板解析类
// +----------------------------------------------------------------------
// | 文 件 名  Template.class.php
// +----------------------------------------------------------------------
// | 作    者  xiaokai
// +----------------------------------------------------------------------
// | 时    间  2009-08-14
// +----------------------------------------------------------------------
!defined('ROOT_PATH') && exit;

class Template
{

	//模板实例
	private static $instance 	= NULL;

	//模板文件路径
	public $templatePath		= '';

	//缓存文件存放路径
	public $cachePath			= '';

	//是否开启缓存
	public $cache				= FALSE;

	//缓存文件有效时间
	public $cacheLifeTime		= 0;

	//模板文件后缀名
	public $templateSuffix 	= '.html';

	//左边界符号
	public $leftTag			= '{';

	//右边界符号
	public $rightTag			= '}';

	//模板变量
	public $tVars			= array();

	//当前模板文件名
	private $templateFile		= '';

	//当前缓存文件名
	private $cacheFile			= '';

	/**
	 * 显示模板
	 *
	 * @access public
	 * @param 	string $file 模板文件名, 不包括路径和后缀
	 * @param 	array  $data 模板变量
	 * @return void
	 */
	public function display($file, $data = array())
	{
		$this->fetch($file, $data, TRUE);
	}

	/**
	 * 返回模板内容
	 *
	 * @access public
	 * @param 	string $file 模板文件名, 不包括路径和后缀
	 * @param 	array  $data 模板变量
	 * @return string
	 */
	public function fetch($file, $data = array(), $display = FALSE)
	{
		$this->cacheLifeTime = $this->cacheLifeTime * 60;
		$this->InitFilePath($file);
		$this->tVars = $data;

		//验证是否需要更新缓存
		if(!$this->CheckCache())
		{
			$this->Cache();
		}

		//将模板变量数组, 导出为变量
		extract($this->tVars);

		//载入模版缓存文件
		ob_start();
		include($this->cacheFile);
		if(!$display)
		{
			$data = ob_get_contents();
			ob_end_clean();
			return $data;
		}
	}

	/**
	 * 缓存模板文件
	 *
	 * @access public
	 * @return void
	 */
	public function Cache()
	{
		$content 	= file_get_contents($this->templateFile);

		//替换系统常量
		$patt      = array('__PUBLIC__', '__ACTION__', '__CONTROL__', '__WEB__', '__APP__');
		$replace  = array('<?php echo(__PUBLIC__); ?>', '<?php echo(__ACTION__); ?>', '<?php echo(__CONTROL__); ?>', '<?php echo(__WEB__); ?>', '<?php echo(__APP__); ?>');
		$content  = str_ireplace('../public', '<?php echo(TEMPLATE_PUBLIC); ?>', $content);
		$content  = str_replace($patt, $replace, $content);


		$patt		= '(' . $this->leftTag . ')(\S.+?)(' . $this->rightTag .')';
		$content	= preg_replace("/{$patt}/eis", "\$this->ParseTag('\\2')", $content);
		$str		= "<?php !defined('ROOT_PATH') && exit; /* Tomorrow Framework 模板缓存文件 生成时间:".date('Y-m-d H:i:s', time()) . "  */ ?>\r\n";
		$content   = $str . $content;
		return file_put_contents($this->cacheFile, $content);
	}

	/**
	 * 解析变量
	 *
	 * @access public
	 * @param  string $varStr 变量字符串
	 * @return string
	 */
	public function ParseVar($varStr)
	{
		static $tVars = array();

		//以|分割字符串, 数组第一位是变量名字符串, 之后的都是函数参数
		$varArray 	= explode('|', $varStr);
		//弹出第一个元素, 也就是变量名
		$var	 	= array_shift($varArray);

		//系统变量
		if(substr($varStr, 0, 2) == 'T.')
		{
			$name = $this->ParseT($var);
		}

		//$var.xxx 方式访问数组或属性
		elseif(strpos($var, '.'))
		{
			$vars = explode('.', $var);
			$name = 'is_array($' . $vars[0] . ') ? $' . $vars[0] . '["' . $vars[1] . '"] : $' . $vars[0] . '->' . $vars[1];
			$var  = $vars[0];
		}

		//$var['xxx'] 方式访问数组
		elseif(strpos($var, '['))
		{
			 $name = "$".$var;
			 preg_match('/(.+?)\[(.+?)\]/is',$var,$arr);
			 $var = $arr[1];
		}
		else
		{
			$name = "$$var";
		}

		//如果有使用函数
		if(count($varArray) > 0)
		{
			//传入变量名, 和函数参数继续解析, 这里的变量名是上面的判断设置的值
			$name	= $this->ParseFunction($name, $varArray);
		}

		$code = !empty($name) ? "<?php echo({$name}); ?>" : '';
		$tVars[$varStr] = $code;
		return $code;
	}

	/**
	 * 解析函数
	 *
	 * @access public
	 * @param  string $varStr 	 变量名称
	 * @param  array  $varArray 函数名称及参数
	 * @return string
	 */
	public function ParseFunction($name, $varArray)
	{
		$len = count($varArray);

		//获取不允许使用的函数
		$not = explode(',', GetC('TEMPLATE_NOT_ALLOWS_FUNC'));
		for($i = 0; $i < $len; $i++)
		{
			//以=分割函数参数, 第一个元素就是函数名, 之后的都是参数
			$arr 		= explode('=', $varArray[$i]);
			$funcName	= array_shift($arr);
			$arr		= array_shift($arr);

			//函数名如果不在不允许使用的函数中
			if(!in_array($funcName, $not))
			{
				$args = explode(',', $arr);
				if(count($arr) > 0)
				{
					$p = array();
					foreach($args as $var)
					{
						$var = trim($var);
						if($var == 'THIS')
						{
								$var = $name;
						}
						$p[] = $var;
					}
					$param = join(", ", $p);
					$code = "{$funcName}($param)";
				}
				else
				{
					$code = "{$funcName}($arr[0])";
				}
			}
		}
		return $code;
	}

	/**
	 * 解析系统变量 $T开头的
	 *
	 * @access public
	 * @param  string $var 	 变量字符串
	 * @return string
	 */
	public function ParseT($var)
	{
		$vars 		= explode('.', $var);
		$vars[1]  	= strtoupper(trim($vars[1]));
		$len		= count($vars);
		$action	= "\$_@";
		if($len >= 3)
		{
			if(in_array($vars[1], array('COOKIE', 'SESSION', 'GET', 'POST', 'SERVER')))
			{
				//替换调名称, 并将使用ArrayHandler函数获取下标, 支持多维
				$code = str_replace('@', $vars[1], $action) . $this->ArrayHandler($vars);
			}
			else if($vars[1] == 'CONFIG' || $vars[1] == 'LANG')
			{
				//这里替换为函数
				$key  = substr($vars[1], 0, 1);
				$code = "Get{$key}('{$vars[2]}')";
			}
			else
			{
				$code = '';
			}
		}
		elseif($len === 2)
		{
			if($vars[1] == 'NOW')
			{
				$code = "date('Y-m-d H:i:s', time())";
			}
			elseif($vars[1] == 'VERSION')
			{
				$code = 'T_VERSION';
			}
			else
			{
				$code = '';
			}
		}
		return $code;
	}

	/**
	 * 构造数组下标
	 *
	 * @access public
	 * @param  string $arr
	 * @param  int	   $go
	 * @return void
	 */
	public function ArrayHandler(&$arr, $go = 2)
	{
		$len = count($arr);
		for($i = $go; $i < $len; $i++)
		{
			$param .= "['{$arr[$i]}']";
		}
		return $param;
	}


	/**
	 * 解析{}中的内容, 根据第一个字符决定使用什么函数进行解析
	 *
	 * @access public
	 * @param  string $label	左右定界符之间的内容
	 * @return void
	 */
	public function ParseTag($label)
	{
		$label	= stripslashes(trim($label));
		$flag	= substr($label, 0, 1);
		$flags  = array('var'	=> '$', 'language' => '@', 'config' => '#', 'cookie' => '+', 'session' => '-', 'get' => '%', 'post' => '&', 'constant' => '*');
		$name   = substr($label, 1);

		//普通变量
		if($flag == $flags['var'])
		{
			return !empty($name) ? $this->ParseVar($name) : NULL;
		}

		//输出语言
		if($flag == $flags['language'])
		{
			return "<?php echo(GetL('{$name}')); ?>";
		}

		//输出配置信息
		if($flag == $flags['config'])
		{
			return "<?php echo(GetC('{$name}')); ?>";
		}

		//输出Cookie
		if($flag == $flags['cookie'])
		{
			return "<?php echo(\$_COOKIE['{$name}']); ?>";
		}

		//输出SESSION
		if($flag == $flags['session'])
		{
			return "<?php echo(\$_SESSION['{$name}']); ?>";
		}

		//输出GET
		if($flag == $flags['get'])
		{
			return "<?php echo(\$_GET['{$name}']); ?>";
		}

		//输出POST
		if($flag == $flags['post'])
		{
			return "<?php echo(\$_POST['{$name}']); ?>";
		}

		//输出常量
		if($flag == $flags['constant'])
		{
			return "<?php echo({$name}); ?>";
		}

		//语句结束部分
		if($flag == '/')
		{
			//foreach结束
			if($name == 'list')
			{
				return "<?php endforeach; endif; ?>";
			}
			return "<?php end{$name}; ?>";
		}

		//foreach 开始
		if(substr($label, 0, 4) == 'list')
		{
			preg_match_all('/\\$([\w]+)/', $label, $arr);
			$arr = $arr[1];
			if(count($arr) > 0)
			{
				return "<?php if(is_array(\${$arr[0]})) : foreach(\${$arr[0]} as \${$arr[1]}) : ?>";
			}
		}

		//if  elseif
		if(substr($label, 0, 2) == 'if' || substr($label, 0, 6) == 'elseif')
		{
			$arr = explode(' ', $name);
			array_shift($arr);
			$param = array();
			foreach($arr as $v)
			{
				if(strpos($v, '.') > 0)
				{
					$args    = explode('.', $v);
					$p 		  = $this->ArrayHandler($args, 1);
					$param[] = $args[0] . $p;
				}
				else
				{
					$param[] = $v;
				}
			}
			$str = join(' ', $param);
			$tag = substr($label, 0, 2) == 'if' ? 'if' : 'elseif';
			return "<?php {$tag}({$str}) :  ?>";
		}

		//else
		if(substr($label, 0, 4) == 'else')
		{
			return "<?php else : ?>";
		}

		return trim($this->leftTag, '\\') . $label . trim($this->rightTag, '\\');
	}


	/**
	 * 验证缓存是否有效
	 *
	 * @access public
	 * @return bool
	 */
	public function CheckCache()
	{
		if(!file_exists($this->cacheFile))
		{
			return FALSE;
		}

		if(filemtime($this->cacheFile) < filemtime($this->templateFile) && $this->cache)
		{
			return FALSE;
		}

		if(filemtime($this->cacheFile) + $this->cacheLifeTime < time() && $this->cache)
		{
			return FALSE;
		}
		return TRUE;
	}


	/**
	 * 初始化模板文件以及缓存文件文件完整路径
	 *
	 * @access public
	 * @param 	string $file 模板文件名, 不包括路径和后缀
	 * @return void
	 */
	private function InitFilePath($file)
	{
		$this->templateFile = $this->templatePath . $file . $this->templateSuffix;
		$this->cacheFile	 = $this->cachePath		. md5($file) . '.php';
	}


	/**
	 * 获取模板类实例
	 *
	 * @access public
	 * @static
	 * @return object
	 */
	public static function GetInstance()
	{
		if(is_null(self::$instance))
		{
			self::$instance = new Template();
		}
		return self::$instance;
	}

	/**
	 * 私有的构造函数, 不允许直接创建对象
	 */
	private function __construct(){}
}


// +----------------------------------------------------------------------
// | 文件结束  Template.class.php
// +----------------------------------------------------------------------