<?php
class Model{
	//数据库连接句柄
	protected $dbLink = null;
	protected $res = null;
	
	
	public function __construct(){
		global $db;
		$this->dbLink = mysql_connect($db['DB_HOST'], $db['DB_USER'], $db['DB_PWD']) or exit(mysql_error());
		mysql_select_db($db['DB_NAME'], $this->dbLink) or exit(mysql_error());
		mysql_query("SET NAMES ".$db['DB_CHAR']);
	}
	
	public function Query($sql){
		$this->res = mysql_query($sql) or exit(mysql_error());
		return $this->res;
	}
	
	public function Execute($sql){
		if(mysql_query($sql)){
			return TRUE;
		}
		return FALSE;
	}

	public function FetchOne($res, $type = 'array'){
		$func = $type == 'array' ? 'mysql_fetch_object' : 'mysql_fetch_row';
		$row  = $func($res);
		return $row;
	}
	
	public function FetchAll($res, $type = 'array'){
		$rows = array();
		$func = $type == 'array' ? 'mysql_fetch_object' : 'mysql_fetch_row';
		while($row = $func($res)){
			$rows[] = $row; 
		}
		return $rows;
	}
	
	
	//释放内存
	public function __destruct(){
		$this->dbLink = null;
		$this->res = null;
	}
}

