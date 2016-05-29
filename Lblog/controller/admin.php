<?php
class AdminController extends Controller{

	/*
	 * login
	 */
	public function index(){
		$this->auth(1);
		$this->display('login');
	}
	
	public function login(){
		$user = addslashes(@$_POST['user']);
		$pass = addslashes(@$_POST['pass']);
		$AdminModel = $this->Model('admin');
		$res = $AdminModel->Query("SELECT * FROM admin");
		$row = $AdminModel->FetchOne($res);
		if($user == $row->name && $pass == $row->pass){
			$_SESSION['login'] = 1;
			$this->redirect("index.php/admin/admin_index");
		}
		else{
			$this->alert("name or pass error!");
		}
	}
	
	public function admin_index(){
		$this->auth();
		$this->display('admin_index');
	}
	
	
	public function logout(){
		$this->auth();
		if(isset($_COOKIE[session_name()]))
		{
			setCookie(session_name(),'',time()-3600,'/');
		}
		session_destroy();
		$this->redirect('index.php/admin');
	}
	
	/*
	 * 文章操作，增删改
	 */
	public function articlesAdd(){
		$this->auth();
		$CategoryShowModel = $this->Model('category');
		$res = $CategoryShowModel->Query("SELECT * FROM category");
		$rows = $CategoryShowModel->FetchAll($res);
		$this->assign('category', $rows);
		$this->display('admin_add_articles');
	}
	public function articlesAddAction(){
		$this->auth();
		$title = addslashes($_POST['title']);
		$content = htmlspecialchars(addslashes($_POST['content']));
		$date = time();
		$tag = addslashes($_POST['tag']);
		$category_id = addslashes($_POST['category_id']);
		$name = 'lemon';
		$articlesAddModel = $this->Model('articles');
		$sql = "INSERT INTO article (`title`,`content`,`date`,`tag`,`category_id`,`name`) VALUES ('".$title."','".$content."','".$date."','".$tag."','".$category_id."','".$name."')";
		if($articlesAddModel->Execute($sql)){
			$this->alert('add successed!');
		}else{
			$this->alert('add failed!');
		}
	}
	public function articlesList(){
		$this->auth();
		$articlesShowModel = $this->Model('articles');
		$res = $articlesShowModel->Query("SELECT article_id,tag,title,category,date FROM `article` inner join category on article.category_id=category.category_id");
		$rows = $articlesShowModel->FetchAll($res);
		$this->assign('articles', $rows);
		$this->display('admin_articles');
	}
	public function articlesMod(){
		global $config;
		$this->auth();
		$article_id = isset($config['params']['id']) ? intval($config['params']['id']) : "1";
		$articlesShowModel = $this->Model('articles');
		$res = $articlesShowModel->Query("SELECT * FROM `article` inner join category on article.category_id=category.category_id where article_id='".$article_id."'");
		$rows = $articlesShowModel->FetchAll($res);
		if(empty($rows)){
			$this->alert('no id');
			exit();
		}
		
		$CategoryShowModel = $this->Model('category');
		$res1 = $CategoryShowModel->Query("SELECT * FROM category");
		$rows1 = $CategoryShowModel->FetchAll($res1);		

		$this->assign('articles', $rows);
		$this->assign('category', $rows1);
		$this->display('admin_mod_articles');
	}
	public function articlesModAction(){
		global $config;
		$this->auth();
		$title = addslashes($_POST['title']);
		$content = htmlspecialchars(addslashes($_POST['content']));
		$date = time();
		$tag = addslashes($_POST['tag']);
		$category_id = intval($_POST['category_id']);
		$articles_id = intval($config['params']['id']);
		
		$articlesModModel = $this->Model('articles');
		$sql = "update article set title='".$title."',content='".$content."',date='".$date."',tag='".$tag."',category_id='".$category_id."' where article_id='".$articles_id."'";
		if($articlesModModel->Execute($sql)){
			$this->alert('mod successed!');
		}else{
			$this->alert('mod failed!');
		}
	}
	public function articlesDel(){
		global $config;
		$this->auth();
		$articlesDelModel = $this->Model('articles');
		$sql = "DELETE FROM article WHERE article_id = '".intval($config['params']['id'])."'";
		if($articlesDelModel->Execute($sql)){
			$this->alert('del successed!');
		}else{
			$this->alert('del failed!');
		}
	}
	
	/*
	 * 公告发布
	 */
	public function notice(){
		$this->auth();
		if(isset($_POST['comment'])){
			$noticeModel = $this->Model('notice');
			$sql = "INSERT INTO notice (`content`,`date`) VALUES ('". addslashes($_POST['comment']) ."','".time()."')";
			if($noticeModel->Execute($sql)){
				$this->alert('add notice successed!');
			}else{
				$this->alert('add  notice failed!');
			}
		}
		$this->display('admin_notice');
	}
	
	/*
	 * 友情链接添加
	 */
	public function link(){
		$this->auth();
		$linkShowModel = $this->Model('link');
		$res = $linkShowModel->Query("SELECT * FROM link");
		$rows = $linkShowModel->FetchAll($res);
		$this->assign('link', $rows);
		$this->display('admin_link');
	}
	public function linkAdd(){
		$this->auth();
		$name = addslashes($_POST['name']);
		$url = addslashes($_POST['url']);
		$linkModel = $this->Model('link');
		$sql = "INSERT INTO link (`name`,`url`) VALUES ('".$name."','".$url."')";
		if($linkModel->Execute($sql)){
			$this->alert('add link successed!');
		}else{
			$this->alert('add link failed!');
		}
	}
	public function linkDel(){
		global $config;
		$this->auth();
		$linkDelModel = $this->Model('link');
		$sql = "DELETE FROM link WHERE id = '".intval($config['params']['id'])."'";
		if($linkDelModel->Execute($sql)){
			$this->alert('del successed!');
		}else{
			$this->alert('del failed!');
		}
	}
	
	/*
	 * 菜单添加
	*/	
	public function category(){
		$this->auth();
		$CategoryShowModel = $this->Model('category');
		$res = $CategoryShowModel->Query("SELECT * FROM category");
		$rows = $CategoryShowModel->FetchAll($res);
		$this->assign('category', $rows);
		$this->display('admin_category');
	}
	public function categoryAdd(){
		$this->auth();
		$category = $_POST['category'];
		$CategoryModel = $this->Model('category');
		$sql = "INSERT INTO category (`category`) VALUES ('".$category."')";
		if($CategoryModel->Execute($sql)){
			$this->alert('add successed!');
		}else{
			$this->alert('add failed!');
		}
	}
	public function categoryDel(){
		global $config;
		$this->auth();
		$CategoryDelModel = $this->Model('category');
		$sql = "DELETE FROM category WHERE category_id = '".intval($config['params']['id'])."'";
		if($CategoryDelModel->Execute($sql)){
			$this->alert('del successed!');
		}else{
			$this->alert('del failed!');
		}	
	}
	
	/*
	 * 留言管理
	 */
	public function message(){
		$this->auth();
		$messageModel = $this->Model('message');
		$res = $messageModel->Query("SELECT * FROM message");
		$rows = $messageModel->FetchAll($res);
		$this->assign('message', $rows);
		
		$this->display('admin_message');
	}
	public function messageDel(){
		global $config;
		$this->auth();
		$messageDelModel = $this->Model('message');
		$sql = "DELETE FROM message WHERE id = '".intval($config['params']['id'])."'";
		if($messageDelModel->Execute($sql)){
			$this->alert('del successed!');
		}else{
			$this->alert('del failed!');
		}
	}
}