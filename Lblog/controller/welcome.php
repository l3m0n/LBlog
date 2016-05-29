<?php

class WelcomeController extends Controller{

	/*
	 * 首页显示
	 */
	public function index(){
		global $config;
		$articlesShowModel = $this->Model('articles');
		if(isset($config['params']['id'])){
			$id = intval($config['params']['id']);
			$res = $articlesShowModel->Query("SELECT article_id,title,content,date,name FROM `article` inner join category on article.category_id=category.category_id where article.category_id=".$id." order by date desc");
		}else{
			$res = $articlesShowModel->Query("SELECT article_id,title,content,date,name FROM `article` inner join category on article.category_id=category.category_id order by date desc");
		}	
		$rows = $articlesShowModel->FetchAll($res);
		$this->assign('articles', $rows);
		
        $this->display('index');
	}
	
	public function search(){
		/*
		 * 有bug，内容url编码，搜索也会有点问题
		 */
		isset($_POST['search']) ? "" : die('No key');
		$search = addslashes($_POST['search']);
		$articlesShowModel = $this->Model('articles');
		$res =  $articlesShowModel->Query("select * from article where title like '%".$search."%' or content like '%".$search."%' or tag like '%".$search."%'");
		$rows = $articlesShowModel->FetchAll($res);
		if(empty($rows)){
			$this->assign('status', '0');
		}else{
			$this->assign('status', '1');
			$this->assign('key',$search);
			$this->assign('search', $rows);
		}
		$this->display('search');
	}

	
	public function articles(){
		global $config;
		$articles_id = isset($config['params']['id']) ? intval($config['params']['id']) : die('No id');
		$articlesShowModel = $this->Model('articles');
		$res = $articlesShowModel->Query("SELECT * FROM `article` where article_id='".$articles_id."'");
		$rows = $articlesShowModel->FetchOne($res);
		$this->assign('articles', $rows);
		
		$messageModel = $this->Model('message');
		$res1 = $messageModel->Query("SELECT * FROM `message` where article_id='".$articles_id."'");
		$rows1 = $messageModel->FetchAll($res1);

		if(empty($rows1)){
			$this->assign('status', 0);
		}else{
			$this->assign('message', $rows1);
		}
		
		
		$this->display('articles');
	}
	
	public function comment(){
		$article_id = @$_POST['article_id'];
		$name = addslashes(@$_POST['name']);
		$mail = addslashes(@$_POST['mail']);
		$comment = addslashes(@$_POST['comment']);
		$articlesShowModel = $this->Model('comment');
		$sql = "INSERT INTO message (`article_id`,`comment`,`date`,`username`,`email`) VALUES ('".$article_id."','".$comment."','".time()."','".$name."','".$mail."')";
		
		if($articlesShowModel->Execute($sql)){
			$this->alert('comment successed!');
		}else{
			$this->alert('comment failed!');
		}
	
		$this->display('articles');
	}
	
	public function navApi(){
		$articlesShowModel = $this->Model('articles');
		$res =  $articlesShowModel->Query("select distinct tag from article order by article_id desc limit 0,12");
		$rows = $articlesShowModel->FetchAll($res);
		$tag = json_encode($rows);
		
		$CategoryShowModel = $this->Model('category');
		$res1 = $CategoryShowModel->Query("SELECT category.category_id,category.category,count(article.article_id) as num FROM `category` left join `article` on category.category_id = article.category_id group by category;");
		$rows1 = $CategoryShowModel->FetchAll($res1);
		$category = json_encode($rows1);
		
		$noticeShowModel = $this->Model('notice');
		$res2 = $noticeShowModel->Query("SELECT * FROM `notice` order by id desc limit 1");
		$rows2 = $noticeShowModel->FetchOne($res2);
		$notice = json_encode($rows2);
		
		$linkShowModel = $this->Model('link');
		$res4 =  $linkShowModel->Query("SELECT * FROM link order by name limit 0,10");
		$rows4 = $linkShowModel->FetchAll($res4);
		$link = json_encode($rows4);
		
		$messageShowModel = $this->Model('message');
		$res5 =  $messageShowModel->Query("SELECT * FROM message order by id desc limit 0,2");
		$rows5 = $messageShowModel->FetchAll($res5);
		$message = json_encode($rows5);
		
		echo '{"tag":'.$tag.',"category":'.$category.',"notice":'.$notice.',"link":'.$link.',"message":'.$message.'}';
	}
}